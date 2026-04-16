<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Tariffs\TariffResource;

use App\Filament\Admin\Resources\Tariffs\Pages\CreateTariff;
use App\Filament\Admin\Resources\Tariffs\Pages\EditTariff;
use App\Filament\Admin\Resources\Tariffs\Pages\ListTariffs;
use App\Filament\Admin\Resources\Tariffs\TariffResource;
use Filament\Resources\Pages\PageRegistration;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(TariffResource::class, 'getPages')]
final class GetPagesTest extends TestCase
{
    public function test_defines_index_create_edit_pages(): void
    {
        $pages = TariffResource::getPages();

        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayHasKey('create', $pages);
        $this->assertArrayHasKey('edit', $pages);
        $this->assertInstanceOf(PageRegistration::class, $pages['index']);
        $this->assertInstanceOf(PageRegistration::class, $pages['create']);
        $this->assertInstanceOf(PageRegistration::class, $pages['edit']);
        $this->assertSame(ListTariffs::class, $pages['index']->getPage());
        $this->assertSame(CreateTariff::class, $pages['create']->getPage());
        $this->assertSame(EditTariff::class, $pages['edit']->getPage());
    }
}

