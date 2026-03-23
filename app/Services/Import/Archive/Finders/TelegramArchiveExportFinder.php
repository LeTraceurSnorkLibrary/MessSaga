<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Finders;

/**
 * Правила поиска export-файла Telegram:
 * 1) result.json
 * 2) любой .json
 */
class TelegramArchiveExportFinder extends AbstractArchiveExportFinder
{
    public function findExportFile(string $absoluteExtractedRoot): ?string
    {
        return $this->findExactName($absoluteExtractedRoot, 'result.json')
            ?? $this->findFirstByExtension($absoluteExtractedRoot, 'json');
    }
}
