<?php

declare(strict_types=1);

namespace App\Services\Import\Export\Locators\Archive;

use App\Services\Import\Export\DTO\ArchiveImportSource;
use App\Services\Import\Export\Locators\ExportFile\TelegramExportFileLocator;

/**
 * Locator для Telegram-архива.
 * Вычисляет media root как директорию рядом с export-файлом.
 */
class TelegramExportArchiveLocator implements ExportArchiveLocatorInterface
{
    /**
     * @param TelegramExportFileLocator $exportFileLocator
     */
    public function __construct(
        private readonly TelegramExportFileLocator $exportFileLocator,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function locate(string $absoluteExtractedRoot): ?ArchiveImportSource
    {
        $relativeExportPath = $this->exportFileLocator->locate($absoluteExtractedRoot);
        if ($relativeExportPath === null) {
            return null;
        }

        $exportDir     = dirname($relativeExportPath);
        $mediaRootPath = $exportDir === '.'
            ? $absoluteExtractedRoot
            : $absoluteExtractedRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $exportDir);

        return new ArchiveImportSource($relativeExportPath, $mediaRootPath);
    }
}
