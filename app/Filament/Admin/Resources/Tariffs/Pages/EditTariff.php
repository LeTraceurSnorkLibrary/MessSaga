<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tariffs\Pages;

use App\Filament\Admin\Resources\Tariffs\TariffResource;
use Filament\Actions\DeleteAction;
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
            DeleteAction::make(),
        ];
    }
}
