<?php

declare(strict_types=1);

namespace App\Utils\Formatters;

final class UrlFormatter
{
    /**
     * Добавляет протокол к ссылке, начинающейся с WWW
     *
     * @param string $url
     *
     * @return string
     */
    public static function normalizeForHref(string $url): string
    {
        if (str_starts_with($url, 'www.')) {
            return 'https://' . $url;
        }

        return $url;
    }
}
