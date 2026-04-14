<?php

declare(strict_types=1);

namespace App\Tariffs;

class Tariff10 extends AbstractTariff
{
    /**
     * @inheritdoc
     */
    public const string TARIFF_NAME = 'tariff_10';

    /**
     * @inheritdoc
     */
    public const string LABEL = 'Тариф 10';

    /**
     * @inheritdoc
     */
    public const int MAX_STORAGE_BYTES = 10 * 1024 * 1024 * 1024;

    /**
     * @inheritdoc
     */
    public const int MAX_MEDIA_FILES_COUNT = 1000;
}

