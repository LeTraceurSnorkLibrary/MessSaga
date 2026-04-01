<?php

declare(strict_types=1);

namespace App\Services\Import\Export\Locators\ExportFile;

/**
 * Контракт поиска файла экспорта в уже распакованном архиве.
 */
interface ExportFileLocatorInterface
{
    /**
     * Возвращает путь к export-файлу относительно корня распаковки.
     *
     * @param string $absoluteExtractedRoot
     *
     * @return string|null
     */
    public function locate(string $absoluteExtractedRoot): ?string;
}
