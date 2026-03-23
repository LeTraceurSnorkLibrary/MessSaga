<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Подготавливает ZIP-импорт: распаковывает архив и определяет export/media root.
 */
class ArchiveImportPreparationService
{
    /**
     * @param ExportArchiveLocatorFactory $locatorFactory
     */
    public function __construct(
        private readonly ExportArchiveLocatorFactory $locatorFactory,
    ) {
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

        $extractedDir          = 'chat_imports/extracted_' . uniqid('', true);
        $extractedAbsolutePath = Storage::path($extractedDir);

        $zip = new ZipArchive();
        if ($zip->open($absoluteZip, ZipArchive::RDONLY) !== true) {
            return [
                'path_to_use'     => null,
                'media_root_path' => null,
                'extracted_dir'   => null,
            ];
        }

        $zip->extractTo($extractedAbsolutePath);
        $zip->close();

        $archiveImportSource = $this->locatorFactory
            ->make($messengerType)
            ->locate($extractedAbsolutePath);

        if ($archiveImportSource === null) {
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
            'path_to_use'     => $extractedDir . '/' . str_replace(
                    DIRECTORY_SEPARATOR,
                    '/',
                    $archiveImportSource->getExportFileRelativePath()
                ),
            'media_root_path' => $archiveImportSource->getMediaRootAbsolutePath(),
            'extracted_dir'   => $extractedDir,
        ];
    }
}
