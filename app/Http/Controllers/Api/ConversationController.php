<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\Parsers\ParserRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Teapot\StatusCode\Http;

class ConversationController extends Controller
{
    /**
     * @param ParserRegistry $parserRegistry
     */
    public function __construct(
        private readonly ParserRegistry $parserRegistry
    ) {
    }

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
                $messengerType = $conversation->messengerAccount->type;

                /**
                 * Получаем парсер из реестра
                 */
                $parser = $this->parserRegistry->get($messengerType);

                /**
                 * Используем метод парсера для получения последнего сообщения
                 */
                $lastMessage = $parser->getMessagesRelation($conversation)
                    ->latest('sent_at')
                    ->first();

                return [
                    'id'      => $conversation->id,
                    'title'   => $conversation->title,
                    'preview' => $lastMessage?->text,
                    'type'    => $messengerType,
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
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, Http::FORBIDDEN);

        $messengerType = $conversation->messengerAccount->type;

        /**
         * Получаем парсер из реестра
         */
        $parser = $this->parserRegistry->get($messengerType);

        /**
         * Получаем сообщения через парсер
         */
        $messages = $parser->getMessagesRelation($conversation)
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
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, Http::FORBIDDEN);

        /**
         * Каскадно удалит сообщения за счёт foreign key
         */
        $conversation->delete();

        return response()->noContent();
    }
}
