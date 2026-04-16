<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Tariffs\TariffResource;

use App\Filament\Admin\Resources\Tariffs\TariffResource;
use Filament\Tables\Table;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(TariffResource::class, 'table')]
final class TableTest extends TestCase
{
    public function test_configures_tariff_table_columns(): void
    {
        $table = $this->createStub(Table::class);
        $table->method('columns')->willReturnSelf();

        $this->assertSame($table, TariffResource::table($table));
    }
}

