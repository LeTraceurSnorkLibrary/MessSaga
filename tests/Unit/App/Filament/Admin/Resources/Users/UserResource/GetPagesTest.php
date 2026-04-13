<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Users\UserResource;

use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Resources\Pages\PageRegistration;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(UserResource::class, 'getPages')]
final class GetPagesTest extends TestCase
{
    public function test_defines_index_page_route(): void
    {
        $pages = UserResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertInstanceOf(PageRegistration::class, $pages['index']);
        $this->assertSame(ListUsers::class, $pages['index']->getPage());
    }
}
