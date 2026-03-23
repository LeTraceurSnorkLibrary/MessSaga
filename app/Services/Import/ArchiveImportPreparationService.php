<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Archive\Factories\ArchiveExportLocatorFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Подготавливает ZIP-импорт: распаковывает архив и определяет export/media root.
 */
class ArchiveImportPreparationService
{
    /**
     * @param ArchiveExportLocatorFactory $locatorFactory
     */
    public function __construct(
        private readonly ArchiveExportLocatorFactory $locatorFactory,
    ) {
    }

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
     * Распаковывает архив и возвращает пути для последующего импорта.
     *
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

        $location = $this->locatorFactory
            ->make($messengerType)
            ->locate($absoluteExtracted);

        if ($location === null) {
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

        return [
            'path_to_use'     => $extractedDir . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $location->relativeExportPath),
            'media_root_path' => $location->absoluteMediaRootPath,
            'extracted_dir'   => $extractedDir,
        ];
    }
}
