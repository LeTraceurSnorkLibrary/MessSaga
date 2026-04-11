<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Enums\UserRoleEnum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsersTable
{
    /**
     * Конфигурирует таблицу "Список пользователей" для Filament-админки
     *
     * @param Table $table
     *
     * @return Table
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label('Роль')
                    ->formatStateUsing(fn(?string $state): string => '')
                    ->badge(
                        fn(Model $record): bool => ($record->role ?? null) !== UserRoleEnum::USER->value
                    )
                    ->icon(fn(?string $state): ?string => match ($state) {
                        UserRoleEnum::ADMIN->value   => 'heroicon-o-at-symbol',
                        UserRoleEnum::MANAGER->value => 'heroicon-o-user-circle',
                        default                      => null,
                    })
                    ->color(fn(?string $state): ?string => match ($state) {
                        UserRoleEnum::ADMIN->value   => 'success',
                        UserRoleEnum::MANAGER->value => 'danger',
                        default                      => null,
                    })
                    ->extraAttributes(fn(Model $record): array => [
                        'title' => match ($record->role ?? null) {
                            UserRoleEnum::ADMIN->value   => 'Администратор',
                            UserRoleEnum::MANAGER->value => 'Менеджер',
                            UserRoleEnum::USER->value    => 'Пользователь',
                            default                      => is_string($record->role)
                                ? $record->role
                                : '',
                        },
                    ])
                    ->sortable(),
                TextColumn::make('messages_count')
                    ->label('Сообщения')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
            ])
            ->toolbarActions([
            ]);
    }
}
