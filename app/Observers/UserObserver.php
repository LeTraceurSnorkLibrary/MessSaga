<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\User;
use App\Services\Quota\TariffChangeGracePeriodService;

/**
 * Реагирует на изменения пользователя, связанные с тарифами и квотами.
 */
class UserObserver
{
    /**
     * @param TariffChangeGracePeriodService $tariffChangeGracePeriodService
     */
    public function __construct(
        private readonly TariffChangeGracePeriodService $tariffChangeGracePeriodService
    ) {
    }

    /**
     * При смене tariff_code пересчитывает льготный период для downgrade с превышением квоты.
     *
     * @param User $user
     *
     * @return void
     */
    public function updating(User $user): void
    {
        if (!$user->isDirty('tariff_code')) {
            return;
        }

        $oldTariffCode = $user->getOriginal('tariff_code');
        $this->tariffChangeGracePeriodService->applyForTariffChange(
            user: $user,
            oldTariffCode: is_string($oldTariffCode)
                ? $oldTariffCode
                : null,
        );
    }
}
