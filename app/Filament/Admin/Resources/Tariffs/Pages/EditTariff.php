<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tariffs\Pages;

use App\Filament\Admin\Resources\Tariffs\TariffResource;
use App\Models\Tariff;
use App\Tariffs\FreeTariff;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\EditRecord;

class EditTariff extends EditRecord
{
    protected static string $resource = TariffResource::class;

    /**
     * @inheritdoc
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn(Tariff $record): bool => $record->name !== FreeTariff::TARIFF_NAME)
                ->form([
                    Select::make('target_tariff_code')
                        ->label('Перенести пользователей на тариф')
                        ->options(fn(Tariff $record): array => Tariff::query()
                            ->where('name', '!=', $record->name)
                            ->orderBy('label')
                            ->pluck('label', 'name')
                            ->all())
                        ->default(FreeTariff::TARIFF_NAME)
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data, Tariff $record): void {
                    $record->reassignmentTargetTariffCode = (string) $data['target_tariff_code'];
                    $record->delete();
                }),
        ];
    }
}
