<?php

declare(strict_types=1);

namespace App\Services\Import\Export\Locators\Archive;

use App\Services\Import\Export\DTO\ArchiveImportSource;
use App\Services\Import\Export\Locators\ExportFile\WhatsAppExportFileLocator;

/**
 * Locator для WhatsApp-архива.
 * Для WhatsApp media root = корень распакованного архива.
 */
class WhatsAppExportArchiveLocator implements ExportArchiveLocatorInterface
{
    /**
     * @param WhatsAppExportFileLocator $finder
     */
    public function __construct(
        private readonly WhatsAppExportFileLocator $finder,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function locate(string $absoluteExtractedRoot): ?ArchiveImportSource
    {
        $relativeExportPath = $this->finder->locate($absoluteExtractedRoot);
        if ($relativeExportPath === null) {
            return null;
        }

        return new ArchiveImportSource($relativeExportPath, $absoluteExtractedRoot);
    }
}
