<?php

declare(strict_types=1);

namespace App\Tariffs;

class Tariff50 extends AbstractTariff
{
    /**
     * @inheritdoc
     */
    public const string TARIFF_NAME = 'tariff_50';

    /**
     * @inheritdoc
     */
    public const string LABEL = 'Тариф 50';

    /**
     * @inheritdoc
     */
    public const int MAX_STORAGE_BYTES = 50 * 1024 * 1024 * 1024;

    /**
     * @inheritdoc
     */
    public const int MAX_MEDIA_FILES_COUNT = 5000;
}

