<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConversationController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $messengerType = $request->string('messenger')->toString();

        $query = Conversation::query()
            ->whereHas('messengerAccount', function (Builder $q) use ($request, $messengerType) {
                $q->where('user_id', $request->user()->id);

                if ($messengerType) {
                    $q->where('type', $messengerType);
                }
            })
            ->with('messengerAccount');

        $conversations = $query
            ->orderByDesc('id')
            ->get()
            ->map(function (Conversation $conversation) {
                // Получаем последнее сообщение в зависимости от типа мессенджера
                // Используем messagesQuery() для получения query builder
                $lastMessage = $conversation->messagesQuery()
                    ->latest('sent_at')
                    ->first();

                return [
                    'id'      => $conversation->id,
                    'title'   => $conversation->title,
                    'preview' => $lastMessage?->text,
                ];
            });

        return response()->json($conversations);
    }

    /**
     * @param Request      $request
     * @param Conversation $conversation
     *
     * @return JsonResponse
     */
    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, 403);

        // Получаем сообщения используя универсальный метод messagesQuery()
        $messages = $conversation->messagesQuery()
            ->orderBy('sent_at')
            ->get(['id', 'sender_name', 'sent_at', 'text', 'message_type']);

        return response()->json($messages);
    }

    /**
     * @param Request      $request
     * @param Conversation $conversation
     *
     * @return Response
     */
    public function destroy(Request $request, Conversation $conversation): Response
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, 403);

        $conversation->delete(); // каскадно удалит сообщения за счёт foreign key

        return response()->noContent();
    }
}
