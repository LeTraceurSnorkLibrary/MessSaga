<?php

declare(strict_types=1);

namespace App\Tariffs;

class FreeTariff extends AbstractTariff
{
    /**
     * @inheritdoc
     */
    public const string TARIFF_NAME = 'tariff_free';

    /**
     * @inheritdoc
     */
    public const string LABEL = 'Бесплатно';

    /**
     * @inheritdoc
     */
    public const int MAX_STORAGE_BYTES = 12 * 1024 * 1024;

    /**
     * @inheritdoc
     */
    public const int MAX_MEDIA_FILES_COUNT = 100;
}

