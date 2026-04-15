<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\User;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Filament\Panel;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

#[CoversMethod(User::class, 'canAccessPanel')]
#[CoversMethod(User::class, 'hasRole')]
final class CanAccessPanelTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_allow_access_for_admin_role(): void
    {
        $panel = $this->createStub(Panel::class);

        $admin       = new User();
        $admin->role = UserRoleEnum::ADMIN->value;

        $this->assertTrue($admin->canAccessPanel($panel));
    }

    /**
     * @throws Exception
     */
    public function test_allow_access_for_manager_role(): void
    {
        $panel = $this->createStub(Panel::class);

        $manager       = new User();
        $manager->role = UserRoleEnum::MANAGER->value;

        $this->assertTrue($manager->canAccessPanel($panel));
    }

    /**
     * @throws Exception
     */
    public function test_disallow_access_for_user_role(): void
    {
        $panel = $this->createStub(Panel::class);

        $user       = new User();
        $user->role = UserRoleEnum::USER->value;

        $this->assertFalse($user->canAccessPanel($panel));
    }
}
