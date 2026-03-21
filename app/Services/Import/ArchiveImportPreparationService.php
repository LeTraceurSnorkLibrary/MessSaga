<?php

declare(strict_types=1);

namespace App\Services\Import;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ArchiveImportPreparationService
{
    /**
     * @param string $path
     *
     * @return bool
     */
    public function isZipPath(string $path): bool
    {
        return str_ends_with(strtolower($path), '.zip');
    }

    /**
     * @param string $storagePath
     * @param string $messengerType
     *
     * @return array{
     *      path_to_use: ?string,
     *      media_root_path: ?string,
     *      extracted_dir: ?string
     *  }
     */
    public function unpackAndLocateExport(string $storagePath, string $messengerType): array
    {
        $absoluteZip = Storage::path($storagePath);
        if (!is_file($absoluteZip)) {
            return [
                'path_to_use'     => null,
                'media_root_path' => null,
                'extracted_dir'   => null,
            ];
        }

        $extractedDir      = 'chat_imports/extracted_' . uniqid('', true);
        $absoluteExtracted = Storage::path($extractedDir);

        $zip = new ZipArchive();
        if ($zip->open($absoluteZip, ZipArchive::RDONLY) !== true) {
            return [
                'path_to_use'     => null,
                'media_root_path' => null,
                'extracted_dir'   => null,
            ];
        }

        $zip->extractTo($absoluteExtracted);
        $zip->close();

        $exportRelativePath = $this->findExportFileByMessenger($absoluteExtracted, $messengerType);
        if ($exportRelativePath === null) {
            Log::warning('ProcessChatImport: export file not found in archive', [
                'path'           => $storagePath,
                'messenger_type' => $messengerType,
                'extracted'      => $extractedDir,
            ]);

            return [
                'path_to_use'     => null,
                'media_root_path' => null,
                'extracted_dir'   => $extractedDir,
            ];
        }

        $relativeExport = $extractedDir . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $exportRelativePath);

        $exportDir     = dirname($exportRelativePath);
        $mediaRootPath = strtolower($messengerType) === 'whatsapp'
            ? $absoluteExtracted
            : ($exportDir === '.'
                ? $absoluteExtracted
                : $absoluteExtracted . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $exportDir));

        return [
            'path_to_use'     => $relativeExport,
            'media_root_path' => $mediaRootPath,
            'extracted_dir'   => $extractedDir,
        ];
    }

    /**
     * @param string $absoluteExtractedRoot
     * @param string $messengerType
     *
     * @return string|null
     */
    private function findExportFileByMessenger(string $absoluteExtractedRoot, string $messengerType): ?string
    {
        $messengerType = strtolower($messengerType);

        if ($messengerType === 'telegram') {
            return $this->findTelegramExportFile($absoluteExtractedRoot, '');
        }
        if ($messengerType === 'whatsapp') {
            return $this->findWhatsAppExportFile($absoluteExtractedRoot, '');
        }

        return $this->findTelegramExportFile($absoluteExtractedRoot, '')
            ?? $this->findWhatsAppExportFile($absoluteExtractedRoot, '');
    }

    /**
     * @param string $absoluteDir
     * @param string $relativePrefix
     *
     * @return string|null
     */
    private function findTelegramExportFile(string $absoluteDir, string $relativePrefix): ?string
    {
        $found = $this->findFileRecursive(
            $absoluteDir,
            $relativePrefix,
            'result.json',
            static fn (string $name): bool => strtolower($name) === 'result.json'
        );
        if ($found !== null) {
            return $found;
        }

        return $this->findFileRecursive(
            $absoluteDir,
            $relativePrefix,
            null,
            static fn (string $name): bool => str_ends_with(strtolower($name), '.json')
        );
    }

    /**
     * @param string $absoluteDir
     * @param string $relativePrefix
     *
     * @return string|null
     */
    private function findWhatsAppExportFile(string $absoluteDir, string $relativePrefix): ?string
    {
        $found = $this->findFileRecursive($absoluteDir, $relativePrefix, null, static function (string $name): bool {
            $lower = strtolower($name);

            return str_ends_with($lower, '.txt') && str_contains($lower, 'whatsapp');
        });
        if ($found !== null) {
            return $found;
        }

        return $this->findFileRecursive(
            $absoluteDir,
            $relativePrefix,
            null,
            static fn (string $name): bool => str_ends_with(strtolower($name), '.txt')
        );
    }

    /**
     * @param string        $absoluteDir
     * @param string        $relativePrefix
     * @param string|null   $exactName
     * @param callable|null $predicate
     *
     * @return string|null
     */
    private function findFileRecursive(
        string    $absoluteDir,
        string    $relativePrefix,
        ?string   $exactName,
        ?callable $predicate
    ): ?string {
        if (!is_dir($absoluteDir)) {
            return null;
        }
        $sep   = DIRECTORY_SEPARATOR;
        $items = @scandir($absoluteDir);
        if ($items === false) {
            return null;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $absoluteDir . $sep . $item;
            if (is_file($full)) {
                if ($exactName !== null && strcasecmp($item, $exactName) === 0) {
                    return $relativePrefix
                        ? $relativePrefix . '/' . $item
                        : $item;
                }
                if ($predicate !== null && $predicate($item)) {
                    return $relativePrefix
                        ? $relativePrefix . '/' . $item
                        : $item;
                }
            }
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $absoluteDir . $sep . $item;
            if (is_dir($full)) {
                $prefix = $relativePrefix
                    ? $relativePrefix . '/' . $item
                    : $item;
                $found  = $this->findFileRecursive($full, $prefix, $exactName, $predicate);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
