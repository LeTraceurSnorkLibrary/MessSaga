<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Contracts;

use App\Services\Import\Archive\DTO\ArchiveExportLocation;

/**
 * Контракт locator-стратегии для конкретного мессенджера.
 *
 * Locator не только находит export-файл, но и вычисляет корректный media root.
 */
interface ArchiveExportLocatorInterface
{
    public function locate(string $absoluteExtractedRoot): ?ArchiveExportLocation;
}
