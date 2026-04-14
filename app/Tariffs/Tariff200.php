<?php

declare(strict_types=1);

namespace App\Tariffs;

class Tariff200 extends AbstractTariff
{
    /**
     * @inheritdoc
     */
    public const string TARIFF_NAME = 'tariff_200';

    /**
     * @inheritdoc
     */
    public const string LABEL = 'Тариф 200';

    /**
     * @inheritdoc
     */
    public const int MAX_STORAGE_BYTES = 200 * 1024 * 1024 * 1024;

    /**
     * @inheritdoc
     */
    public const int MAX_MEDIA_FILES_COUNT = 20000;
}

