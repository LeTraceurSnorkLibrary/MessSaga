<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tariffs\Pages;

use App\Filament\Admin\Resources\Tariffs\TariffResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTariffs extends ListRecords
{
    protected static string $resource = TariffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
