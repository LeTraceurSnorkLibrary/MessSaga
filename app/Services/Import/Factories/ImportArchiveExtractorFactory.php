<?php

declare(strict_types=1);

namespace App\Services\Import\Factories;

use App\Services\Import\Archives\ImportArchiveExtractorInterface;

/**
 * Реестр/factory preparer-сервисов по формату архива.
 */
class ImportArchiveExtractorFactory
{
    /**
     * @var array<int, ImportArchiveExtractorInterface>
     */
    private array $preparers = [];

    /**
     * @param ImportArchiveExtractorInterface $preparer
     *
     * @return $this
     */
    public function register(ImportArchiveExtractorInterface $preparer): self
    {
        $this->preparers[] = $preparer;

        return $this;
    }

    /**
     * @param string $storagePath
     *
     * @return ImportArchiveExtractorInterface|null
     */
    public function makeForPath(string $storagePath): ?ImportArchiveExtractorInterface
    {
        foreach ($this->preparers as $preparer) {
            if ($preparer->supports($storagePath)) {
                return $preparer;
            }
        }

        return null;
    }
}
