<?php

declare(strict_types=1);

namespace App\Services\Import\Archive\Factories;

use App\Services\Import\Archive\Contracts\ArchiveExportLocatorInterface;
use RuntimeException;

/**
 * Реестр/factory locator-стратегий по типу мессенджера.
 *
 * Добавление нового мессенджера = register('new_type', NewLocator::class).
 */
class ArchiveExportLocatorFactory
{
    /**
     * @var array<string, ArchiveExportLocatorInterface>
     */
    private array $locators = [];

    /**
     * @var ArchiveExportLocatorInterface|null
     */
    private ?ArchiveExportLocatorInterface $fallback = null;

    /**
     * Регистрирует новый тип локатора для указанного типа мессенджера.
     *
     * @param string                        $messengerType
     * @param ArchiveExportLocatorInterface $locator
     *
     * @return $this
     */
    public function register(string $messengerType, ArchiveExportLocatorInterface $locator): self
    {
        $this->locators[$messengerType] = $locator;

        return $this;
    }

    /**
     * Регистрирует фоллбэк-локатор.
     *
     * @param ArchiveExportLocatorInterface $locator
     *
     * @return $this
     */
    public function setFallback(ArchiveExportLocatorInterface $locator): self
    {
        $this->fallback = $locator;

        return $this;
    }

    /**
     * @param string $messengerType
     *
     * @return ArchiveExportLocatorInterface
     */
    public function make(string $messengerType): ArchiveExportLocatorInterface
    {
        if (isset($this->locators[$messengerType])) {
            return $this->locators[$messengerType];
        }
        if ($this->fallback !== null) {
            return $this->fallback;
        }

        throw new RuntimeException("No archive export locator registered for messenger type: $messengerType");
    }
}
