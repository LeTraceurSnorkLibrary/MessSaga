<?php

declare(strict_types=1);

namespace App\Models\MessageTypes;

enum WhatsAppMessageTypesEnum: string
{
    case TEXT = 'text';
    case MEDIA = 'media';
}
