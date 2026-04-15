<?php

declare(strict_types=1);

namespace App\Tariffs;

use App\Tariffs\Contracts\TariffInterface;

abstract class AbstractTariff implements TariffInterface
{
    /**
     * Tariff's stringed ID
     */
    public const string TARIFF_NAME = '';

    /**
     * Tariff's label (human-readable)
     */
    public const string LABEL = '';

    /**
     * Uploaded media files limit in bytes
     */
    public const int MAX_STORAGE_BYTES = 0;

    /**
     * Uploaded media files count limit
     */
    public const int MAX_MEDIA_FILES_COUNT = 0;

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
        return static::LABEL;
    }

    /**
     * @inheritdoc
     */
    public function getMaxStorageBytes(): int
    {
        return static::MAX_STORAGE_BYTES;
    }

    /**
     * @inheritdoc
     */
    public function getMaxMediaFilesCount(): int
    {
        return static::MAX_MEDIA_FILES_COUNT;
    }

    /**
     * @inheritdoc
     */
    public function allowsMediaUpload(): bool
    {
        return $this->getMaxStorageBytes() > 0 && $this->getMaxMediaFilesCount() > 0;
    }
}
