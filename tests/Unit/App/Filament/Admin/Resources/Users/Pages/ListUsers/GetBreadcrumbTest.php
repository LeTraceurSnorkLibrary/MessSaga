<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Users\Pages\ListUsers;

use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(ListUsers::class, 'getBreadcrumb')]
final class GetBreadcrumbTest extends TestCase
{
    public function test_returns_expected_breadcrumb(): void
    {
        $page = new ListUsers();

        $this->assertSame('Список', $page->getBreadcrumb());
    }
}
