<?php

declare(strict_types=1);

namespace App\Tariffs;

use App\Models\Tariff;
use App\Tariffs\Contracts\TariffInterface;

final readonly class DatabaseTariff implements TariffInterface
{
    /**
     * @param Tariff $tariff
     */
    public function __construct(
        private Tariff $tariff,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return (string)$this->tariff->name;
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return (string)$this->tariff->label;
    }

    /**
     * @inheritdoc
     */
    public function allowsMediaUpload(): bool
    {
        return $this->getMaxStorageBytes() > 0 && $this->getMaxMediaFilesCount() > 0;
    }

    /**
     * @inheritdoc
     */
    public function getMaxStorageBytes(): int
    {
        return max(0, (int)$this->tariff->max_storage_mb) * 1024 * 1024;
    }

    /**
     * @inheritdoc
     */
    public function getMaxMediaFilesCount(): int
    {
        return max(0, (int)$this->tariff->max_media_files_count);
    }
}

