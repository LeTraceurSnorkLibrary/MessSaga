<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Locators;

use App\Services\Import\Archive\Contracts\ArchiveExportLocatorInterface;
use App\Services\Import\Archive\DTO\ArchiveExportLocation;
use App\Services\Import\Archive\Finders\TelegramArchiveExportFinder;

/**
 * Locator для Telegram-архива.
 * Вычисляет media root как директорию рядом с export-файлом.
 */
class TelegramArchiveExportLocator implements ArchiveExportLocatorInterface
{
    public function __construct(
        private readonly TelegramArchiveExportFinder $finder,
    ) {
    }

    public function locate(string $absoluteExtractedRoot): ?ArchiveExportLocation
    {
        $relativeExportPath = $this->finder->findExportFile($absoluteExtractedRoot);
        if ($relativeExportPath === null) {
            return null;
        }

        $exportDir     = dirname($relativeExportPath);
        $mediaRootPath = $exportDir === '.'
            ? $absoluteExtractedRoot
            : $absoluteExtractedRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $exportDir);

        return new ArchiveExportLocation($relativeExportPath, $mediaRootPath);
    }
}
