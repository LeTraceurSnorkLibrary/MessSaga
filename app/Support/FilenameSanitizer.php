<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Очистка имён файлов от спецсимволов (LRM, RLM, нулевая ширина и т.д.) для безопасного хранения.
 */
final class FilenameSanitizer
{
    /**
     * Sanitizes filename
     *
     * @param string $filename
     *
     * @return string
     */
    public static function sanitize(string $filename): string
    {
        $cleaned = preg_replace('/[^a-zA-Z0-9\._\-]+/u', '_', $filename);
        $cleaned = preg_replace('/_+/', '_', $cleaned ?? '');
        $cleaned = trim($cleaned ?? '', '_');

        return $cleaned !== ''
            ? $cleaned
            : 'file';
    }
}
