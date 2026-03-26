<?php

declare(strict_types=1);

namespace App\Services\Import\Archives;

use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Подготавливает ZIP-архив импорта:
 * распаковка + определение export-файла и корня медиа.
 */
class ZipImportArchiveExtractor implements ImportArchiveExtractorInterface
{
    /**
     * Максимально допустимое количество файлов внутри архива.
     */
    private const MAX_ARCHIVE_ENTRIES = 10000;

    /**
     * Максимально допустимый размер одного файла внутри архива
     */
    private const MAX_SINGLE_FILE_BYTES = 32 * 1024 * 1024; // 32 MB

    /**
     * Максимально допустимый суммарный размер всех файлов распакованного архива
     */
    private const MAX_TOTAL_UNCOMPRESSED_BYTES = 1024 * 1024 * 1024; // 1 GB

    /**
     * @param ExportArchiveLocatorFactory $locatorFactory
     */
    public function __construct(
        private readonly ExportArchiveLocatorFactory $locatorFactory,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function supports(string $storagePath): bool
    {
        return str_ends_with(strtolower($storagePath), '.zip');
    }

    /**
     * @inheritdoc
     */
    public function extract(string $storagePath, string $messengerType): ArchiveExtractionResult
    {
        $unpacked     = $this->extractArchiveOnly($storagePath);
        $extractedDir = $unpacked->getExtractedDir();
        if ($extractedDir === null) {
            throw new ArchiveExtractionFailedException(
                'ZIP extraction succeeded but extracted directory is missing'
            );
        }
        $extractedAbsolutePath = Storage::path($extractedDir);

        $archiveImportSource = $this->locatorFactory
            ->make($messengerType)
            ->locate($extractedAbsolutePath);

        if ($archiveImportSource === null) {
            Log::warning('ZipImportArchiveExtractor: export file not found in archive', [
                'path'           => $storagePath,
                'messenger_type' => $messengerType,
                'extracted'      => $extractedDir,
            ]);

            /**
             * ZIP может быть "медиа-архивом" без текстового export-файла.
             * В этом случае отдадим хотя бы каталог для последующего сопоставления медиа
             */
            return new ArchiveExtractionResult(
                null,
                $extractedAbsolutePath,
                $extractedDir
            );
        }

        return new ArchiveExtractionResult(
            Storage::path($extractedDir . '/' . $archiveImportSource->getExportFileRelativePath()),
            $archiveImportSource->getMediaRootAbsolutePath(),
            $extractedDir
        );
    }

    /**
     * Универсальная распаковка ZIP во временный каталог без поиска export-файла.
     *
     * @param string $storagePath
     *
     * @return ArchiveExtractionResult
     */
    private function extractArchiveOnly(string $storagePath): ArchiveExtractionResult
    {
        $absoluteZip = Storage::path($storagePath);
        if (!is_file($absoluteZip)) {
            throw new ArchiveExtractionFailedException(
                'ZIP file not found'
            );
        }

        $extractedDir          = 'chat_imports/extracted_' . uniqid('', true);
        $extractedAbsolutePath = Storage::path($extractedDir);

        $zip = new ZipArchive();
        if ($zip->open($absoluteZip, ZipArchive::RDONLY) !== true) {
            throw new ArchiveExtractionFailedException(
                'Failed to open ZIP archive'
            );
        }

        try {
            $this->guardArchiveLimits($zip);

            if (!$this->extractSafely($zip, $extractedAbsolutePath)) {
                throw new ArchiveExtractionFailedException(
                    'ZIP archive contains unsafe entries or cannot be extracted'
                );
            }
        } catch (ArchiveExtractionFailedException $e) {
            if (Storage::exists($extractedDir)) {
                Storage::deleteDirectory($extractedDir);
            }

            throw $e;
        } finally {
            $zip->close();
        }

        return new ArchiveExtractionResult(
            null,
            null,
            $extractedDir
        );
    }

    /**
     * Ограничивает распаковку по объему и количеству файлов.
     *
     * @param ZipArchive $zip
     *
     * @throws ArchiveExtractionFailedException
     * @return void
     */
    private function guardArchiveLimits(ZipArchive $zip): void
    {
        if ($zip->numFiles > self::MAX_ARCHIVE_ENTRIES) {
            throw new ArchiveExtractionFailedException('ZIP archive has too many entries');
        }

        $totalUncompressedSize = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                throw new ArchiveExtractionFailedException('Failed to read ZIP entry metadata');
            }

            $size = $stat['size'] ?? null;
            if (!is_int($size)) {
                throw new ArchiveExtractionFailedException('Invalid ZIP entry size metadata');
            }

            if ($size < 0 || $size > self::MAX_SINGLE_FILE_BYTES) {
                throw new ArchiveExtractionFailedException('ZIP entry exceeds allowed size');
            }

            $totalUncompressedSize += $size;
            if ($totalUncompressedSize > self::MAX_TOTAL_UNCOMPRESSED_BYTES) {
                throw new ArchiveExtractionFailedException('ZIP archive exceeds total uncompressed size limit');
            }
        }
    }

    /**
     * Защищённая распаковка ZIP: запрещаем path traversal и абсолютные пути.
     *
     * @param ZipArchive $zip
     * @param string     $destinationAbsolutePath
     *
     * @return bool
     */
    private function extractSafely(ZipArchive $zip, string $destinationAbsolutePath): bool
    {
        if (!is_dir($destinationAbsolutePath) && !mkdir($destinationAbsolutePath, 0775, true) && !is_dir($destinationAbsolutePath)) {
            return false;
        }

        $baseRealPath = realpath($destinationAbsolutePath);
        if ($baseRealPath === false) {
            return false;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (!is_string($entryName) || $this->isUnsafeZipEntryName($entryName)) {
                return false;
            }

            if ($this->isSymlinkEntry($zip, $i)) {
                return false;
            }

            $normalized = trim(str_replace('\\', '/', $entryName), '/');
            if ($normalized === '') {
                continue;
            }

            $isDirectory = str_ends_with(str_replace('\\', '/', $entryName), '/');
            $targetPath  = $destinationAbsolutePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);

            $parentPath = $isDirectory
                ? $targetPath
                : dirname($targetPath);

            if (!is_dir($parentPath) && !mkdir($parentPath, 0775, true) && !is_dir($parentPath)) {
                return false;
            }

            $parentRealPath = realpath($parentPath);
            if (!$this->isPathInsideBase($parentRealPath, $baseRealPath)) {
                return false;
            }

            if ($isDirectory) {
                continue;
            }

            if (is_link($targetPath)) {
                return false;
            }

            $input = $zip->getStream($entryName);
            if (!is_resource($input)) {
                return false;
            }

            $output = fopen($targetPath, 'wb');
            if (!is_resource($output)) {
                fclose($input);

                return false;
            }

            $copied = stream_copy_to_stream($input, $output);
            fclose($input);
            fclose($output);

            if ($copied === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|false $path
     * @param string       $baseRealPath
     *
     * @return bool
     */
    private function isPathInsideBase(string|false $path, string $baseRealPath): bool
    {
        if (!is_string($path)) {
            return false;
        }

        if ($path === $baseRealPath) {
            return true;
        }

        $baseWithSeparator = rtrim($baseRealPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($path . DIRECTORY_SEPARATOR, $baseWithSeparator);
    }

    /**
     * @param ZipArchive $zip
     * @param int        $index
     *
     * @return bool
     */
    private function isSymlinkEntry(ZipArchive $zip, int $index): bool
    {
        $opsys = 0;
        $attr  = 0;
        $flags = 0;

        if (!$zip->getExternalAttributesIndex($index, $opsys, $attr, $flags)) {
            return false;
        }

        if ($opsys !== ZipArchive::OPSYS_UNIX) {
            return false;
        }

        $unixMode = ($attr >> 16) & 0xF000;

        return $unixMode === 0xA000;
    }

    /**
     * Проверка имени файла внутри ZIP на traversal/absolute path.
     *
     * @param string $entryName
     *
     * @return bool
     */
    private function isUnsafeZipEntryName(string $entryName): bool
    {
        if ($entryName === '' || str_contains($entryName, "\0")) {
            return true;
        }

        if (preg_match('/^[a-zA-Z]:[\\\\\\/]/', $entryName) === 1) {
            return true;
        }

        $normalized = str_replace('\\', '/', $entryName);
        if (str_starts_with($normalized, '/')) {
            return true;
        }

        $parts = explode('/', $normalized);
        if (in_array('..', $parts, true)) {
            return true;
        }

        return false;
    }
}
