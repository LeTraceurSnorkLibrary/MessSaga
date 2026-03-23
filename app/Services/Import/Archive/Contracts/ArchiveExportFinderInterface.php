<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Contracts;

/**
 * Контракт поиска файла экспорта в уже распакованном архиве.
 */
interface ArchiveExportFinderInterface
{
    /**
     * Возвращает путь к export-файлу относительно корня распаковки.
     *
     * @param string $absoluteExtractedRoot
     *
     * @return string|null
     */
    public function findExportFile(string $absoluteExtractedRoot): ?string;
}
