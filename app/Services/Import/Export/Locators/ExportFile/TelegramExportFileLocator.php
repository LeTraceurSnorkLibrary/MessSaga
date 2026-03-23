<?php

declare(strict_types=1);

namespace App\Services\Import\Export\Locators\ExportFile;

/**
 * Правила поиска export-файла Telegram:
 * 1) result.json
 * 2) любой .json
 */
class TelegramExportFileLocator extends AbstractExportFileLocator implements ExportFileLocatorInterface
{
    public function locate(string $absoluteExtractedRoot): ?string
    {
        return $this->findExactName($absoluteExtractedRoot, 'result.json')
            ?? $this->findFirstByExtension($absoluteExtractedRoot, 'json');
    }
}
