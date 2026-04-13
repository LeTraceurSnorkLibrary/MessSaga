<?php

declare(strict_types=1);

namespace Tests\Unit\App\Providers\Filament\AdminPanelProvider;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

#[CoversMethod(AdminPanelProvider::class, 'panel')]
final class PanelTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_configures_panel_and_returns_same_instance(): void
    {
        $panel = $this->createStub(Panel::class);

        $panel->method('id')->willReturnSelf();
        $panel->method('path')->willReturnSelf();
        $panel->method('favicon')->willReturnSelf();
        $panel->method('homeUrl')->willReturnSelf();
        $panel->method('colors')->willReturnSelf();
        $panel->method('discoverResources')->willReturnSelf();
        $panel->method('discoverPages')->willReturnSelf();
        $panel->method('discoverWidgets')->willReturnSelf();
        $panel->method('widgets')->willReturnSelf();
        $panel->method('middleware')->willReturnSelf();
        $panel->method('authMiddleware')->willReturnSelf();
        $panel->method('renderHook')->willReturnSelf();

        $provider = new AdminPanelProvider($this->app);

        $this->assertSame($panel, $provider->panel($panel));
    }
}
