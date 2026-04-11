<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users;

use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Filament\Admin\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Support\Admin\Statistics\UserMessageCountAggregates;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Пользователи';

    protected static ?string $pluralModelLabel = 'Пользователи';

    protected static ?string $modelLabel = 'Пользователь';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return UserMessageCountAggregates::withTotalMessagesCount(
            parent::getEloquentQuery()
        );
    }
}
