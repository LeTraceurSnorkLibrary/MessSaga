<?php

declare(strict_types=1);

namespace App\Services\Import\Export\Locators\ExportFile;

/**
 * Правила поиска export-файла WhatsApp:
 * 1) .txt с "whatsapp" в имени
 * 2) любой .txt
 */
class WhatsAppExportFileLocator extends AbstractExportFileLocator implements ExportFileLocatorInterface
{
    public function locate(string $absoluteExtractedRoot): ?string
    {
        return $this->findFirstTxtContaining($absoluteExtractedRoot, 'whatsapp')
            ?? $this->findFirstByExtension($absoluteExtractedRoot, 'txt');
    }
}
