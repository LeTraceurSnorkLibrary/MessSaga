<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Users\UserResource;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Tables\Table;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(UserResource::class, 'table')]
final class TableTest extends TestCase
{
    public function test_delegates_table_configuration_to_users_table_helper(): void
    {
        $table = $this->createStub(Table::class);

        $table->method('columns')->willReturnSelf();
        $table->method('filters')->willReturnSelf();
        $table->method('recordActions')->willReturnSelf();
        $table->method('toolbarActions')->willReturnSelf();

        $this->assertSame($table, UserResource::table($table));
    }
}
