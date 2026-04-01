<?php

declare(strict_types=1);

namespace App\Models\MediaTypes;

/**
 * Перечисление всех доступных типов медиа-файлов - они будут отображаться
 */
enum SupportedMediaTypesEnum: string
{
    case IMAGE = 'image';
    case AUDIO = 'audio';
    case VIDEO = 'video';

    public static function detect(?string $mimeType, ?string $path = null): ?self
    {
        if (is_string($mimeType) && $mimeType !== '') {
            if (str_starts_with($mimeType, 'image/')) {
                return self::IMAGE;
            }
            if (str_starts_with($mimeType, 'audio/')) {
                return self::AUDIO;
            }
            if (str_starts_with($mimeType, 'video/')) {
                return self::VIDEO;
            }
        }

        if (!is_string($path) || $path === '') {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($ext) {
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'heic', 'heif' => self::IMAGE,
            'mp3', 'm4a', 'aac', 'ogg', 'oga', 'wav', 'opus'           => self::AUDIO,
            'mp4', 'mov', 'webm', 'mkv', 'avi', 'm4v'                  => self::VIDEO,
            default                                                    => null,
        };
    }
}
