<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Finders;

/**
 * Правила поиска export-файла WhatsApp:
 * 1) .txt с "whatsapp" в имени
 * 2) любой .txt
 */
class WhatsAppArchiveExportFinder extends AbstractArchiveExportFinder
{
    public function findExportFile(string $absoluteExtractedRoot): ?string
    {
        return $this->findFirstTxtContaining($absoluteExtractedRoot, 'whatsapp')
            ?? $this->findFirstByExtension($absoluteExtractedRoot, 'txt');
    }
}
