<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\DTO;

/**
 * Результат поиска export-файла в распакованном архиве.
 *
 * relativeExportPath используется для передачи файла в ImportService через Storage.
 * absoluteMediaRootPath задаёт корень, откуда далее ищутся медиа-вложения.
 */
final readonly class ArchiveExportLocation
{
    public function __construct(
        public string $relativeExportPath,
        public string $absoluteMediaRootPath,
    ) {
    }
}
