<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRoleEnum: string
{
    case ADMIN   = 'admin';
    case MANAGER = 'manager';
    case USER    = 'user';
}
