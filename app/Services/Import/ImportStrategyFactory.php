<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\DTO\ImportModeDTO;
use App\Services\Import\Strategies\AutoImportStrategy;
use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\Import\Strategies\NewImportStrategy;
use App\Services\Import\Strategies\SelectImportStrategy;
use InvalidArgumentException;

class ImportStrategyFactory
{
    /**
     * @var array<string, ImportStrategyInterface>
     */
    private array $strategies = [];

    public function __construct()
    {
        $this->register(new AutoImportStrategy());
        $this->register(new NewImportStrategy());
        $this->register(new SelectImportStrategy());
    }

    /**
     * @param ImportStrategyInterface $strategy
     *
     * @return $this
     */
    public function register(ImportStrategyInterface $strategy): static
    {
        $this->strategies[$strategy->getName()] = $strategy;

        return $this;
    }

    /**
     * @param ImportModeDTO $modeDTO
     *
     * @return ImportStrategyInterface
     */
    public function createStrategy(ImportModeDTO $modeDTO): ImportStrategyInterface
    {
        $mode = $modeDTO->getMode();

        if (!isset($this->strategies[$mode])) {
            throw new InvalidArgumentException("Unknown import mode: {$mode}");
        }

        $strategy = clone $this->strategies[$mode];

        if ($strategy instanceof SelectImportStrategy) {
            $strategy->setImportMode($modeDTO);
        }

        return $strategy;
    }
}
