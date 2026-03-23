<?php

declare(strict_types=1);

namespace App\Services\Import\Export\Locators\Archive;

use App\Services\Import\Export\DTO\ArchiveImportSource;

/**
 * Контракт locator-стратегии для конкретного мессенджера.
 *
 * Locator не только находит export-файл, но и вычисляет корректный media root.
 */
interface ExportArchiveLocatorInterface
{
    /**
     * @param string $absoluteExtractedRoot
     *
     * @return ArchiveImportSource|null
     */
    public function locate(string $absoluteExtractedRoot): ?ArchiveImportSource;
}
