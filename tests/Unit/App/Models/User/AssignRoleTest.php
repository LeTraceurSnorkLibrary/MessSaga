<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\User;

use App\Enums\UserRoleEnum;
use App\Models\User;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(User::class, 'assignRole')]
final class AssignRoleTest extends TestCase
{
    public function test_assign_role_updates_model_and_returns_same_instance(): void
    {
        $user       = new User();
        $user->role = UserRoleEnum::USER->value;

        $returned = $user->assignRole(UserRoleEnum::ADMIN->value);

        $this->assertSame($user, $returned);
        $this->assertSame(UserRoleEnum::ADMIN->value, $user->role);
    }
}
