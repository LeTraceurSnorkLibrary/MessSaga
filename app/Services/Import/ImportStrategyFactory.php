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
     * @var int
     */
    protected int $forUserId;

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
    public function register(ImportStrategyInterface $strategy): self
    {
        $this->strategies[$strategy->getName()] = $strategy;

        return $this;
    }

    /**
     * @param int $requestUserId
     *
     * @return $this
     */
    public function forUserId(int $requestUserId): static
    {
        $this->forUserId = $requestUserId;

        return $this;
    }

    /**
     * @param ImportModeDTO $importModeDTO
     *
     * @throws InvalidArgumentException
     * @return ImportStrategyInterface
     */
    public function getStrategy(ImportModeDTO $importModeDTO): ImportStrategyInterface
    {
        $mode = $importModeDTO->getMode();
        if (!isset($this->strategies[$mode])) {
            throw new InvalidArgumentException("Unknown import mode: {$mode}");
        }

        $strategy = $this->strategies[$mode];
        if ($strategy instanceof SelectImportStrategy) {
            $strategy->setTargetConversationId($importModeDTO->getTargetConversationId());
            if (isset($this->forUserId)) {
                $strategy->setUserId($this->forUserId);
            }
        }

        return $strategy;
    }
}
