<?php

declare(strict_types=1);

namespace App\Tariffs;

use App\Tariffs\Contracts\TariffInterface;

abstract class AbstractTariff implements TariffInterface
{
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
    abstract public function getMaxStorageBytes(): int;

    /**
     * @inheritdoc
     */
    abstract public function getMaxMediaFilesCount(): int;
}
