<?php

declare(strict_types=1);

namespace App\Services\Import\Archives;

use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
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
        $unpacked              = $this->extractArchiveOnly($storagePath);
        $extractedAbsolutePath = $unpacked->getMediaRootPath();
        $extractedDir          = $unpacked->getExtractedDir();
        if ($extractedAbsolutePath === null || $extractedDir === null) {
            return ArchiveExtractionResult::notPrepared();
        }

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
            return ArchiveExtractionResult::notPrepared();
        }

        $extractedDir          = 'chat_imports/extracted_' . uniqid('', true);
        $extractedAbsolutePath = Storage::path($extractedDir);

        $zip = new ZipArchive();
        if ($zip->open($absoluteZip, ZipArchive::RDONLY) !== true) {
            return ArchiveExtractionResult::notPrepared();
        }

        if (!$this->extractSafely($zip, $extractedAbsolutePath)) {
            $zip->close();
            Log::warning('ZipImportArchiveExtractor: unsafe archive entries detected', [
                'path' => $storagePath,
            ]);

            return ArchiveExtractionResult::notPrepared();
        }
        $zip->close();

        return new ArchiveExtractionResult(
            null,
            $extractedAbsolutePath,
            $extractedDir
        );
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

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);
            if (!is_string($entryName) || $this->isUnsafeZipEntryName($entryName)) {
                return false;
            }
        }

        return $zip->extractTo($destinationAbsolutePath);
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
