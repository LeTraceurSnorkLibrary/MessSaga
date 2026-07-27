<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Services\User\UserNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserNotificationController extends Controller
{
    public function __construct(
        private readonly UserNotificationService $userNotificationService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $user       = $request->user();
        $onlyUnread = $request->boolean('unread');

        if ($onlyUnread) {
            return response()->json([
                'notifications' => $this->userNotificationService->listUnreadForUser($user),
            ]);
        }

        $notifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(static fn(UserNotification $notification): array => [
                'id'         => $notification->id,
                'type'       => $notification->type,
                'message'    => $notification->message,
                'payload'    => $notification->payload,
                'read_at'    => $notification->read_at?->toIso8601String(),
                'created_at' => $notification->created_at?->toIso8601String(),
            ])
            ->all();

        return response()->json(['notifications' => $notifications]);
    }

    public function markRead(Request $request, UserNotification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $this->userNotificationService->markRead($request->user(), $notification->id);

        return response()->json(['status' => 'ok']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $count = $this->userNotificationService->markAllRead($request->user());

        return response()->json([
            'status' => 'ok',
            'marked' => $count,
        ]);
    }
}
