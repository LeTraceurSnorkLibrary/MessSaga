<?php

declare(strict_types=1);

namespace App\Services\Quota;

use App\Models\User;
use App\Tariffs\TariffCatalog;

/**
 * Переносит пользователей с одного тарифа на другой с пересчётом grace-периода.
 *
 * Используется как fallback при удалении тарифа и как точка расширения для
 * сценария "админ выбирает тариф-приёмник" в UI удаления.
 */
final readonly class TariffUserReassignmentService
{
    /**
     * @param TariffChangeGracePeriodService $gracePeriodService
     */
    public function __construct(
        private TariffChangeGracePeriodService $gracePeriodService,
    ) {
    }

    /**
     * Переназначает пользователей с тарифа-источника на тариф-приёмник.
     *
     * Если `$targetTariffCode` не задан или невалиден - используется "Бесплатный"
     * через {@see TariffCatalog::forCode()}.
     *
     * @param string      $sourceTariffCode код удаляемого/заменяемого тарифа
     * @param string|null $targetTariffCode код тарифа-приёмника
     *
     * @return int количество обновлённых пользователей
     */
    public function reassignUsersFromTariffCode(string $sourceTariffCode, ?string $targetTariffCode = null): int
    {
        $sourceTariffCode = trim($sourceTariffCode);
        if ($sourceTariffCode === '') {
            return 0;
        }

        $resolvedTargetTariffCode = TariffCatalog::forCode($targetTariffCode)->getName();
        if ($resolvedTargetTariffCode === $sourceTariffCode) {
            return 0;
        }

        $updatedUsers = 0;

        User::query()
            ->where('tariff_code', $sourceTariffCode)
            ->eachById(function (User $user) use ($sourceTariffCode, $resolvedTargetTariffCode, &$updatedUsers): void {
                $user->tariff_code = $resolvedTargetTariffCode;
                $this->gracePeriodService->applyForTariffChange(
                    user: $user,
                    oldTariffCode: $sourceTariffCode,
                );
                $user->save();
                $updatedUsers++;
            });

        return $updatedUsers;
    }
}
