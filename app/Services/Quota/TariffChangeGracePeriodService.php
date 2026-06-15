<?php

declare(strict_types=1);

namespace App\Services\Quota;

use App\Models\User;
use App\Tariffs\Contracts\TariffInterface;
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
     * @param UserMediaQuotaService $userMediaQuotaService
     */
    public function __construct(
        private readonly UserMediaQuotaService $userMediaQuotaService,
    ) {
    }

    /**
     * Пересчитывает льготный период при смене тарифа пользователя.
     *
     * Льготный период назначается только при downgrade: новый тариф строго меньше
     * по объёму хранилища или по числу файлов, и текущее использование не влезает в новые лимиты.
     * При апгрейде grace-период не создаётся, но и не сбрасывается, пока пользователь
     * остаётся за пределами лимитов нового тарифа.
     *
     * @param User        $user              пользователь с уже установленным новым tariff_code
     * @param string|null $oldTariffCode     код тарифа до изменения
     * @param int|null    $graceDaysOverride длительность льготного периода в сутках (для тестов/админки)
     *
     * @return void
     */
    public function applyForTariffChange(
        User    $user,
        ?string $oldTariffCode,
        ?int    $graceDaysOverride = null,
    ): void {
        $this->applyForQuotaDowngrade(
            user: $user,
            oldTariff: TariffCatalog::forCode($oldTariffCode),
            graceDaysOverride: $graceDaysOverride,
        );
    }

    /**
     * Пересчитывает льготный период, когда лимиты текущего тарифа изменились.
     *
     * Используется для сценария редактирования самого тарифа в админке.
     * Пользователь остаётся на том же tariff_code, но новый тариф может стать "хуже" прежнего.
     *
     * @param User            $user              пользователь на редактируемом тарифе
     * @param TariffInterface $oldTariff         снимок тарифа до изменения лимитов
     * @param int|null        $graceDaysOverride длительность льготного периода в сутках
     */
    public function applyForTariffLimitsChange(
        User            $user,
        TariffInterface $oldTariff,
        ?int            $graceDaysOverride = null,
    ): void {
        $this->applyForQuotaDowngrade(
            user: $user,
            oldTariff: $oldTariff,
            graceDaysOverride: $graceDaysOverride,
        );
    }

    /**
     * Универсальный расчёт grace-периода относительно предыдущих лимитов.
     *
     * @param User            $user
     * @param TariffInterface $oldTariff
     * @param int|null        $graceDaysOverride
     *
     * @return void
     */
    private function applyForQuotaDowngrade(
        User            $user,
        TariffInterface $oldTariff,
        ?int            $graceDaysOverride = null,
    ): void {
        $newTariff = $user->tariff();

        $snapshot = $this->userMediaQuotaService->snapshot($user);
        if ($snapshot->canUploadMedia()) {
            $user->media_quota_grace_until = null;

            return;
        }

        $isNewStorageBytesLessThanOld = $newTariff->getMaxStorageBytes() < $oldTariff->getMaxStorageBytes();

        $isNewFilesCountLessThanOld = $newTariff->getMaxMediaFilesCount() < $oldTariff->getMaxMediaFilesCount();

        $isDowngrade = $isNewStorageBytesLessThanOld || $isNewFilesCountLessThanOld;
        if (!$isDowngrade) {
            return;
        }

        $graceDays = $graceDaysOverride ?? $this->defaultGracePeriodDays;
        $graceDays = max(0, $graceDays);

        $user->media_quota_grace_until = CarbonImmutable::now()->addDays($graceDays);
    }
}
