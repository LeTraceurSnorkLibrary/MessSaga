<?php

declare(strict_types=1);

namespace App\Services\Import\Export\Factories;

use App\Services\Import\Export\Locators\Archive\ExportArchiveLocatorInterface;
use RuntimeException;

/**
 * Реестр/factory locator-стратегий по типу мессенджера.
 *
 * Добавление нового мессенджера = register('new_type', NewLocator::class).
 */
class ExportArchiveLocatorFactory
{
    /**
     * @var array<string, ExportArchiveLocatorInterface>
     */
    private array $locators = [];

    /**
     * Регистрирует новый тип локатора для указанного типа мессенджера.
     *
     * @param string                        $messengerType
     * @param ExportArchiveLocatorInterface $locator
     *
     * @return $this
     */
    public function register(string $messengerType, ExportArchiveLocatorInterface $locator): static
    {
        $this->locators[$messengerType] = $locator;

        return $this;
    }

    /**
     * @param string $messengerType
     *
     * @throws RuntimeException
     * @return ExportArchiveLocatorInterface
     */
    public function make(string $messengerType): ExportArchiveLocatorInterface
    {
        if (isset($this->locators[$messengerType])) {
            return $this->locators[$messengerType];
        }

        throw new RuntimeException("No ExportArchiveLocator registered for messenger type: $messengerType");
    }
}
