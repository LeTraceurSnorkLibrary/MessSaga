<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\User;

use App\Enums\UserRoleEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(User::class, 'hasRole')]
final class HasRoleTest extends TestCase
{
    public function test_returns_true_only_for_matching_role(): void
    {
        $user       = new User();
        $user->role = UserRoleEnum::ADMIN->value;

        $this->assertTrue($user->hasRole(UserRoleEnum::ADMIN->value));
        $this->assertFalse($user->hasRole(UserRoleEnum::MANAGER->value));
    }
}
