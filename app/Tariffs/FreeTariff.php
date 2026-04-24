<?php

declare(strict_types=1);

namespace App\Tariffs;

class FreeTariff extends AbstractTariff
{
    /**
     * Tariff's stringed ID
     */
    public const string TARIFF_NAME = 'tariff_free';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return static::TARIFF_NAME;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return 'Бесплатный';
    }

    /**
     * @inheritdoc
     */
    public function getMaxStorageBytes(): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function getMaxMediaFilesCount(): int
    {
        return 0;
    }
}
