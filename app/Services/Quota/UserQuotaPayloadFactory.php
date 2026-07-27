<?php

declare(strict_types=1);

namespace App\Services\Quota;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Строит единый payload квоты пользователя для Inertia и API.
 */
final class UserQuotaPayloadFactory
{
    /**
     * @param UserMediaQuotaService $userMediaQuotaService
     */
    public function __construct(
        private readonly UserMediaQuotaService $userMediaQuotaService,
    ) {
    }

    /**
     * @param User $user
     *
     * @return array<string, mixed>
     */
    public function make(User $user): array
    {
        $snapshot   = $this->userMediaQuotaService->snapshot($user)->toArray();
        $graceUntil = $user->media_quota_grace_until;

        return array_merge($snapshot, [
            'grace' => [
                'until'        => $graceUntil instanceof Carbon
                    ? $graceUntil->toIso8601String()
                    : null,
                'seconds_left' => $graceUntil instanceof Carbon
                    ? max(0, Carbon::now()->diffInSeconds($graceUntil, false))
                    : null,
                'is_active'    => $graceUntil instanceof Carbon
                    ? $graceUntil->isFuture()
                    : false,
            ],
        ]);
    }
}
