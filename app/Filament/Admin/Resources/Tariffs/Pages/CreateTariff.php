<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Tariffs\Pages;

use App\Filament\Admin\Resources\Tariffs\TariffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTariff extends CreateRecord
{
    protected static string $resource = TariffResource::class;
}

