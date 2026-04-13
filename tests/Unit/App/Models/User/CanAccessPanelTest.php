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
    public function test_allows_access_only_for_admin_role(): void
    {
        $panel = $this->createStub(Panel::class);

        $admin       = new User();
        $admin->role = UserRoleEnum::ADMIN->value;

        $manager       = new User();
        $manager->role = UserRoleEnum::MANAGER->value;

        $this->assertTrue($admin->canAccessPanel($panel));
        $this->assertFalse($manager->canAccessPanel($panel));
    }
}
