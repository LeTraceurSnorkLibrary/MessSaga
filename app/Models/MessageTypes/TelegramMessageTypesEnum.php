<?php

declare(strict_types=1);

namespace App\Models\MessageTypes;

enum TelegramMessageTypesEnum: string
{
    case MESSAGE = 'message';
    case SERVICE = 'service';
    case BOT_SERVICE = 'bot_service';
    case UNKNOWN = 'unknown';

    /**
     * @param string $value
     *
     * @return self
     */
    public static function fromExportType(string $value): self
    {
        return match ($value) {
            self::MESSAGE->value     => self::MESSAGE,
            self::SERVICE->value     => self::SERVICE,
            self::BOT_SERVICE->value => self::BOT_SERVICE,
            default                  => self::UNKNOWN,
        };
    }
}
