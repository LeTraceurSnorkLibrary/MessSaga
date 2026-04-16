<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tariffs;

use App\Filament\Admin\Resources\Tariffs\Pages\CreateTariff;
use App\Filament\Admin\Resources\Tariffs\Pages\EditTariff;
use App\Filament\Admin\Resources\Tariffs\Pages\ListTariffs;
use App\Models\Tariff;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TariffResource extends Resource
{
    protected static ?string $model = Tariff::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Тарифы';

    protected static ?string $pluralModelLabel = 'Тарифы';

    protected static ?string $modelLabel = 'Тариф';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('ID тарифа')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->rule('regex:/^[a-zA-Z0-9_-]+$/'),
            TextInput::make('label')
                ->label('Метка тарифа (короткое название)')
                ->required()
                ->maxLength(255),
            TextInput::make('price')
                ->label('Цена (руб/месяц)')
                ->required()
                ->numeric()
                ->minValue(0.01)
                ->step(0.01)
                ->inputMode('decimal'),
            ViewField::make('max_storage_mb')
                ->label('Лимит места')
                ->required()
                ->view('filament.admin.tariffs.forms.storage-progress-field'),
            ViewField::make('max_media_files_count')
                ->label('Лимит файлов')
                ->required()
                ->view('filament.admin.tariffs.forms.files-progress-field'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Name')
                ->sortable()
                ->searchable(),
            TextColumn::make('label')
                ->label('Описание')
                ->sortable()
                ->searchable(),
            TextColumn::make('price')
                ->label('Цена')
                ->numeric(decimalPlaces: 2)
                ->sortable(),
            TextColumn::make('max_storage_mb')
                ->label('Место, МБ')
                ->numeric()
                ->sortable(),
            TextColumn::make('max_media_files_count')
                ->label('Файлы')
                ->numeric()
                ->sortable(),
            TextColumn::make('created_at')
                ->label('Создан')
                ->dateTime('d.m.Y H:i')
                ->sortable(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTariffs::route('/'),
            'create' => CreateTariff::route('/create'),
            'edit'   => EditTariff::route('/{record}/edit'),
        ];
    }
}

