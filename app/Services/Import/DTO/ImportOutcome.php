<?php

declare(strict_types=1);

namespace App\Services\Import\DTO;

final readonly class ImportOutcome
{
    public function __construct(
        public int  $messagesImported,
        public int  $mediaSkippedDueToQuota,
        public bool $hadMediaCandidates,
    ) {
    }

    public static function empty(): self
    {
        return new self(0, 0, false);
    }

    public function shouldNotifyQuotaMediaSkipped(): bool
    {
        return $this->hadMediaCandidates && $this->mediaSkippedDueToQuota > 0;
    }
}
