<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Tables;

use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

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
                ViewColumn::make('name')
                    ->label('Имя')
                    ->view('filament.admin.users.columns.editable-text-cell')
                    ->searchable()
                    ->sortable(),
                ViewColumn::make('email')
                    ->label('Email')
                    ->view('filament.admin.users.columns.editable-text-cell')
                    ->searchable()
                    ->sortable(),
                ViewColumn::make('role')
                    ->label('Роль')
                    ->view('filament.admin.users.columns.role-badge-editor')
                    ->sortable(),
                TextColumn::make('messages_count')
                    ->label('Сообщения')
                    ->sortable(),
                ViewColumn::make('tariff_code')
                    ->label('Действующий тариф')
                    ->view('filament.admin.users.columns.editable-tariff-cell')
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
                Action::make('toggleEdit')
                    ->label('')
                    ->icon(fn(ListUsers $livewire, User $record): string => $livewire->isEditingRecord($record)
                        ? 'heroicon-o-check'
                        : 'heroicon-o-pencil-square')
                    ->iconButton()
                    ->tooltip(fn(ListUsers $livewire, User $record): string => $livewire->isEditingRecord($record)
                        ? 'Сохранить'
                        : 'Редактировать')
                    ->color(fn(ListUsers $livewire, User $record): string => $livewire->isEditingRecord($record)
                        ? 'success'
                        : 'gray')
                    ->action(function (ListUsers $livewire, User $record): void {
                        if ($livewire->isEditingRecord($record)) {
                            $livewire->saveEditingRecord($record);

                            return;
                        }

                        $livewire->startEditingRecord($record);
                    }),
            ])
            ->toolbarActions([
            ]);
    }
}
