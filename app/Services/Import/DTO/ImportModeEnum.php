<?php

namespace App\Services\Import\DTO;

enum ImportModeEnum: string
{
    case NEW = 'new';
    case SELECT = 'select';
    case AUTO = 'auto';
}
