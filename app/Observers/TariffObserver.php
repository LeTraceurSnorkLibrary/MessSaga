<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Tariff;
use App\Models\User;
use App\Services\Quota\TariffChangeGracePeriodService;
use App\Services\Quota\TariffUserReassignmentService;
use App\Tariffs\DatabaseTariff;

/**
 * Реагирует на изменение/удаление тарифов.
 *
 * - При снижении лимитов тарифа пересчитывает grace-период всем пользователям на нём.
 * - При удалении тарифа переносит пользователей на fallback-тариф (по умолчанию Free).
 */
final readonly class TariffObserver
{
    /**
     * @param TariffChangeGracePeriodService $gracePeriodService
     * @param TariffUserReassignmentService  $userReassignmentService
     */
    public function __construct(
        private TariffChangeGracePeriodService $gracePeriodService,
        private TariffUserReassignmentService  $userReassignmentService,
    ) {
    }

    /**
     * После обновления лимитов тарифа пересчитывает grace-период всем его пользователям.
     * Для снимка старых лимитов используется {@see Tariff::getPrevious()}.
     *
     * @param Tariff $tariff
     *
     * @return void
     */
    public function updated(Tariff $tariff): void
    {
        $wasStorageBytesChanged  = $tariff->wasChanged('max_storage_mb');
        $wasMaxFilesCountChanged = $tariff->wasChanged('max_media_files_count');
        if (!$wasStorageBytesChanged && !$wasMaxFilesCountChanged) {
            return;
        }

        $previous  = $tariff->getPrevious();
        $oldModel  = new Tariff()
            ->fill([
                'name'                  => (string)($previous['name'] ?? $tariff->name),
                'label'                 => (string)($previous['label'] ?? $tariff->label),
                'price'                 => (string)($previous['price'] ?? $tariff->price),
                'max_storage_mb'        => (int)($previous['max_storage_mb'] ?? $tariff->max_storage_mb),
                'max_media_files_count' => (int)($previous['max_media_files_count'] ?? $tariff->max_media_files_count),
            ]);
        $oldTariff = new DatabaseTariff($oldModel);

        User::query()
            ->where('tariff_code', (string)$tariff->name)
            ->eachById(function (User $user) use ($oldTariff): void {
                $this->gracePeriodService->applyForTariffLimitsChange(
                    user: $user,
                    oldTariff: $oldTariff,
                );

                if ($user->isDirty('media_quota_grace_until')) {
                    $user->save();
                }
            });
    }

    /**
     * При удалении тарифа переводит всех пользователей на fallback-тариф.
     *
     * Сейчас fallback по умолчанию - Free.
     * Для UI удаления можно передать альтернативный код через TariffUserReassignmentService заранее.
     *
     * @param Tariff $tariff
     *
     * @return void
     */
    public function deleting(Tariff $tariff): void
    {
        $this->userReassignmentService->reassignUsersFromTariffCode((string)$tariff->name);
    }
}
