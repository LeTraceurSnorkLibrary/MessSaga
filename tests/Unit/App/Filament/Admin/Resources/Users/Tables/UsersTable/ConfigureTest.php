<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Users\Tables\UsersTable;

use App\Filament\Admin\Resources\Users\Tables\UsersTable;
use Filament\Actions\Action;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

#[CoversMethod(UsersTable::class, 'configure')]
final class ConfigureTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_configures_expected_columns_and_table_sections(): void
    {
        $table = $this->createMock(Table::class);

        $table->expects($this->once())
            ->method('columns')
            ->with($this->callback(static function (array $columns): bool {
                if (count($columns) !== 7) {
                    return false;
                }

                return array_all($columns, static fn($column): bool => $column instanceof Column);
            }))
            ->willReturnSelf();
        $table->expects($this->once())->method('filters')->with([])->willReturnSelf();
        $table->expects($this->once())
            ->method('recordActions')
            ->with($this->callback(static function (array $actions): bool {
                return count($actions) === 1 && $actions[0] instanceof Action;
            }))
            ->willReturnSelf();
        $table->expects($this->once())->method('toolbarActions')->with([])->willReturnSelf();

        $this->assertSame($table, UsersTable::configure($table));
    }
}
