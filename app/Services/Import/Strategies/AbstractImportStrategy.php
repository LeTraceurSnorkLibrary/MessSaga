<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

use App\Services\Import\DTO\ImportModeDTO;

abstract class AbstractImportStrategy implements ImportStrategyInterface
{
    /**
     * Contains import strategy name for getName() method
     */
    public const IMPORT_STRATEGY_NAME = '';

    /**
     * @var ImportModeDTO
     */
    protected ImportModeDTO $importMode;

    /**
     * @return ImportModeDTO|null
     */
    public function getImportMode(): ?ImportModeDTO
    {
        return $this->importMode ?? null;
    }

    /**
     * @param ImportModeDTO $importMode
     *
     * @return $this
     */
    public function setImportMode(ImportModeDTO $importMode): static
    {
        $this->importMode = $importMode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return static::IMPORT_STRATEGY_NAME;
    }
}
