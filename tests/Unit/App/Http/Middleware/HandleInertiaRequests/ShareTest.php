<?php

declare(strict_types=1);

namespace Tests\Unit\App\Http\Middleware\HandleInertiaRequests;

use App\Enums\UserRoleEnum;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

#[CoversMethod(HandleInertiaRequests::class, 'share')]
#[CoversMethod(User::class, 'canAccessPanel')]
#[CoversMethod(User::class, 'hasRole')]
final class ShareTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_shares_admin_panel_url_for_admin_user(): void
    {
        $panel = $this->createStub(Panel::class);
        $panel->method('getUrl')->willReturn('/manage');

        Filament::shouldReceive('getPanel')->times(2)->with('admin')->andReturn($panel);

        $user       = new User();
        $user->role = UserRoleEnum::ADMIN->value;

        $request = Request::create('/');
        $request->setUserResolver(static fn(): User => $user);

        $shared = new HandleInertiaRequests()->share($request);

        $this->assertSame($user, $shared['auth']['user']);
        $this->assertSame('/manage', $shared['filament']['adminPanelUrl']);
    }

    /**
     * @throws Exception
     */
    public function test_sets_admin_panel_url_to_null_for_non_admin_user(): void
    {
        $panel = $this->createStub(Panel::class);
        Filament::shouldReceive('getPanel')->once()->with('admin')->andReturn($panel);

        $user       = new User();
        $user->role = UserRoleEnum::USER->value;

        $request = Request::create('/');
        $request->setUserResolver(static fn(): User => $user);

        $shared = new HandleInertiaRequests()->share($request);

        $this->assertSame($user, $shared['auth']['user']);
        $this->assertNull($shared['filament']['adminPanelUrl']);
    }
}
