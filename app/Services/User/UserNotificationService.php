<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;

final class UserNotificationService
{
    /**
     * @param array<string, mixed>|null $payload
     */
    public function notify(User $user, string $type, string $message, ?array $payload = null): UserNotification
    {
        return UserNotification::query()->create([
            'user_id' => $user->id,
            'type'    => $type,
            'message' => $message,
            'payload' => $payload,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listUnreadForUser(User $user, int $limit = 20): array
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(static fn(UserNotification $notification): array => [
                'id'         => $notification->id,
                'type'       => $notification->type,
                'message'    => $notification->message,
                'payload'    => $notification->payload,
                'created_at' => $notification->created_at?->toIso8601String(),
            ])
            ->all();
    }

    public function markRead(User $user, int $notificationId): bool
    {
        $updated = UserNotification::query()
            ->where('user_id', $user->id)
            ->whereKey($notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);

        return $updated > 0;
    }

    public function markAllRead(User $user): int
    {
        return UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }
}
