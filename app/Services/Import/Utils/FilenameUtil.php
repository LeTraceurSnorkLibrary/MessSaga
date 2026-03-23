<?php

declare(strict_types=1);

namespace App\Services\Import\Utils;

/**
 * Утилиты для работы с путями архивов импорта.
 */
final class FilenameUtil
{
    /**
     * Определяет, что путь указывает на ZIP-архив.
     *
     * @param string $path
     *
     * @return bool
     */
    public static function isZipPath(string $path): bool
    {
        return str_ends_with(strtolower($path), '.zip');
    }
}
