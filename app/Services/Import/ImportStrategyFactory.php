<?php

declare(strict_types=1);

namespace App\Services\Import;

use App\Services\Import\Strategies\AutoImportStrategy;
use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\Import\Strategies\NewImportStrategy;
use App\Services\Import\Strategies\SelectImportStrategy;
use InvalidArgumentException;

class ImportStrategyFactory
{
    /** @var array<string, ImportStrategyInterface> */
    private array $strategies = [];

    public function __construct()
    {
        // Регистрируем все доступные стратегии
        $this->register(new AutoImportStrategy());
        $this->register(new NewImportStrategy());
        $this->register(new SelectImportStrategy());
    }

    /**
     * Регистрирует стратегию
     */
    public function register(ImportStrategyInterface $strategy): self
    {
        $this->strategies[$strategy->getName()] = $strategy;

        return $this;
    }

    /**
     * Возвращает стратегию по имени режима
     *
     * @param string $mode
     *
     * @throws InvalidArgumentException
     * @return ImportStrategyInterface
     */
    public function getStrategy(string $mode): ImportStrategyInterface
    {
        if (!isset($this->strategies[$mode])) {
            throw new InvalidArgumentException("Unknown import mode: {$mode}");
        }

        return $this->strategies[$mode];
    }
}
