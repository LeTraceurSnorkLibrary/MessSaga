<?php

declare(strict_types=1);

namespace App\Services\Quota\Cleanup;

use App\Services\Quota\Cleanup\Contracts\MediaCleanupOrderingStrategyInterface;
use App\Services\Quota\Cleanup\Strategies\LargestMediaCleanupOrderingStrategy;
use App\Services\Quota\Cleanup\Strategies\NewestMediaCleanupOrderingStrategy;
use App\Services\Quota\Cleanup\Strategies\OldestMediaCleanupOrderingStrategy;
use App\Services\Quota\Cleanup\Strategies\SmallestMediaCleanupOrderingStrategy;

final class MediaCleanupOrderingStrategyFactory
{
    /**
     * @var string
     */
    private string $defaultStrategyCode = 'newest';

    /**
     * @var array<string, MediaCleanupOrderingStrategyInterface>|null
     */
    private ?array $map = null;

    /**
     * @param string|null $strategyCode
     *
     * @return MediaCleanupOrderingStrategyInterface
     */
    public function make(?string $strategyCode): MediaCleanupOrderingStrategyInterface
    {
        $resolvedCode = strtolower(trim((string)$strategyCode));
        if ($resolvedCode === '') {
            $resolvedCode = $this->defaultStrategyCode;
        }

        $map = $this->map();

        return $map[$resolvedCode] ?? $map[$this->defaultStrategyCode];
    }

    /**
     * @return array<string, MediaCleanupOrderingStrategyInterface>
     */
    private function map(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        $strategies = [
            new NewestMediaCleanupOrderingStrategy(),
            new OldestMediaCleanupOrderingStrategy(),
            new LargestMediaCleanupOrderingStrategy(),
            new SmallestMediaCleanupOrderingStrategy(),
        ];

        $this->map = [];
        foreach ($strategies as $strategy) {
            $this->map[$strategy->code()] = $strategy;
        }

        return $this->map;
    }
}
