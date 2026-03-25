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

            return ArchiveExtractionResult::notPrepared($extractedDir);
        }

        return new ArchiveExtractionResult(
            Storage::path($extractedDir . '/' . $archiveImportSource->getExportFileRelativePath()),
            $archiveImportSource->getMediaRootAbsolutePath(),
            $extractedDir
        );
    }
}
