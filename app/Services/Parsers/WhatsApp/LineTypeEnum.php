<?php

declare(strict_types=1);

namespace App\Services\Parsers\WhatsApp;

enum LineTypeEnum: string
{
    case MESSAGE      = 'message';
    case SYSTEM       = 'system';
    case CONTINUATION = 'continuation';

    /**
     * @return bool
     */
    public function isNewMessage(): bool
    {
        return $this === self::MESSAGE || $this === self::SYSTEM;
    }
}
