<?php

declare(strict_types=1);

namespace App\Services\Quota;

use App\Models\User;
use App\Tariffs\TariffCatalog;
use Carbon\CarbonImmutable;

class TariffChangeGracePeriodService
{
    /**
     * @var int
     */
    private int $defaultGracePeriodDays = 7;

    /**
     * @param User        $user
     * @param string|null $oldTariffCode
     * @param int|null    $graceDaysOverride
     *
     * @return void
     */
    public function applyForTariffChange(
        User $user,
        ?string $oldTariffCode,
        ?int $graceDaysOverride = null
    ): void {
        $newTariff = $user->tariff();
        $oldTariff = TariffCatalog::forCode($oldTariffCode);

        $isDowngrade = $newTariff->getMaxStorageBytes() < $oldTariff->getMaxStorageBytes()
            || $newTariff->getMaxMediaFilesCount() < $oldTariff->getMaxMediaFilesCount();

        if (!$isDowngrade) {
            $user->media_quota_grace_until = null;

            return;
        }

        $snapshot = $this->userMediaQuotaService->snapshot($user);
        if ($snapshot->canUploadMedia()) {
            $user->media_quota_grace_until = null;

            return;
        }

        $graceDays = $graceDaysOverride ?? $this->defaultGracePeriodDays;
        $graceDays = max(0, $graceDays);

        $user->media_quota_grace_until = CarbonImmutable::now()->addDays($graceDays);
    }

    /**
     * @param UserMediaQuotaService $userMediaQuotaService
     */
    public function __construct(
        private readonly UserMediaQuotaService $userMediaQuotaService
    ) {
    }
}
