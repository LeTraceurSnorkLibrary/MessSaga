<?php

declare(strict_types=1);

namespace App\Tariffs;

use App\Models\Tariff;
use App\Tariffs\Contracts\TariffInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class TariffCatalog
{
    /**
     * @return array<string, TariffInterface>
     */
    public static function all(): array
    {
        $freeTariff = new FreeTariff();
        $result     = [
            $freeTariff->getName() => $freeTariff,
        ];

        foreach (self::databaseTariffs() as $tariff) {
            if ($tariff->getName() === $freeTariff->getName()) {
                continue;
            }

            $result[$tariff->getName()] = $tariff;
        }

        return $result;
    }

    /**
     * @param string|null $code
     *
     * @return TariffInterface
     */
    public static function forCode(?string $code): TariffInterface
    {
        if (!is_string($code) || $code === '') {
            return new FreeTariff();
        }

        return self::all()[$code] ?? new FreeTariff();
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::all() as $tariff) {
            $options[$tariff->getName()] = $tariff->getLabel();
        }

        return $options;
    }

    /**
     * @return list<TariffInterface>
     */
    private static function databaseTariffs(): array
    {
        if (!Schema::hasTable('tariffs')) {
            return [];
        }

        try {
            return Tariff::query()
                ->orderBy('price')
                ->orderBy('id')
                ->get()
                ->map(static fn(Tariff $tariff): TariffInterface => new DatabaseTariff($tariff))
                ->all();
        } catch (QueryException) {
            return [];
        }
    }
}
