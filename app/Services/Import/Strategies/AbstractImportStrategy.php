<?php

declare(strict_types=1);

namespace App\Services\Import\Strategies;

abstract class AbstractImportStrategy implements ImportStrategyInterface
{
    /**
     * Contains import strategy name for getName() method
     */
    public const IMPORT_STRATEGY_NAME = '';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return static::IMPORT_STRATEGY_NAME;
    }
}
