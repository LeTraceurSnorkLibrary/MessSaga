<?php

declare(strict_types=1);

namespace App\Services\Quota;

use App\Models\User;
use App\Tariffs\TariffCatalog;
use Carbon\CarbonImmutable;

/**
 * Управляет льготным периодом после понижения тарифа (downgrade).
 *
 * Если новые лимиты не покрывают уже загруженные медиа, пользователю выставляется
 * {@see User::$media_quota_grace_until} — до этой даты существующие файлы не удаляются
 * командой {@see EnforceExpiredMediaQuotaCommand}, но загрузка новых медиа уже недоступна
 * (проверяется через {@see UserMediaQuotaSnapshot::canUploadMedia()}).
 */
class TariffChangeGracePeriodService
{
    /**
     * @var int срок льготного периода по умолчанию (в сутках), если не передан override.
     */
    private int $defaultGracePeriodDays = 7;

    /**
     * Пересчитывает льготный период при смене тарифа пользователя.
     *
     * Льготный период назначается только при downgrade: новый тариф строго меньше
     * по объёму хранилища или по числу файлов, и текущее использование не влезает
     * в новые лимиты. При апгрейде или если медиа всё ещё укладывается в квоту —
     * {@see User::$media_quota_grace_until} сбрасывается.
     *
     * @param User        $user              пользователь с уже установленным новым tariff_code
     * @param string|null $oldTariffCode     код тарифа до изменения
     * @param int|null    $graceDaysOverride длительность льготного периода в сутках (для тестов/админки)
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
