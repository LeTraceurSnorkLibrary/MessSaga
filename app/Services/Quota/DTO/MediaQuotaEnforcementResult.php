<?php

declare(strict_types=1);

namespace App\Services\Quota\DTO;

final readonly class MediaQuotaEnforcementResult
{
    public function __construct(
        public int  $deletedCount,
        public int  $failedDeletes,
        public bool $stillOverQuota,
    ) {
    }

    public function hasOperationalFailure(): bool
    {
        return $this->failedDeletes > 0 || $this->stillOverQuota;
    }
}
