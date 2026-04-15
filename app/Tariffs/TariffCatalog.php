<?php

declare(strict_types=1);

namespace App\Tariffs;

use App\Tariffs\Contracts\TariffInterface;

class TariffCatalog
{
    /**
     * @return array<string, TariffInterface>
     */
    public static function all(): array
    {
        $tariffs = [
            new FreeTariff(),
            new Tariff10(),
            new Tariff50(),
            new Tariff200(),
        ];

        $result = [];
        foreach ($tariffs as $tariff) {
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
}
