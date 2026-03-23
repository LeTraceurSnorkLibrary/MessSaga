<?php

declare(strict_types=1);

namespace App\Services\Import\Archives;

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
    public function extract(string $storagePath, string $messengerType): array
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
