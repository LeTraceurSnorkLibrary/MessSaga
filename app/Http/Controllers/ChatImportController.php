<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessChatImport;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Teapot\StatusCode\Http;

class ChatImportController extends Controller
{
    /**
     * Запускает импорт переписки с учётом выбранного режима.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'messenger_type'         => 'required|string|in:telegram,whatsapp,viber',
            'file'                   => 'required|file',
            'import_mode'            => 'required|string|in:auto,new,select',
            'target_conversation_id' => 'nullable|integer|exists:conversations,id',
        ]);

        /**
         * Если режим 'select', проверяем наличие ID и принадлежность пользователю
         */
        if ($data['import_mode'] === 'select') {
            $request->validate([
                'target_conversation_id' => 'required|integer|exists:conversations,id',
            ]);

            $conversation = Conversation::where('id', $data['target_conversation_id'])
                ->whereHas('messengerAccount', fn ($q) => $q->where('user_id', $request->user()->id))
                ->first();

            if (!$conversation) {
                return response()->json([
                    'error' => 'Выбранная переписка не принадлежит вам.',
                ], Http::FORBIDDEN);
            }
        }

        $path = $request->file('file')->store('chat_imports');

        ProcessChatImport::dispatch(
            userId: $request->user()->id,
            messengerType: $data['messenger_type'],
            path: $path,
            importMode: $data['import_mode'],
            targetConversationId: $data['target_conversation_id'] ?? null,
        );

        return response()->json([
            'status' => 'queued',
        ]);
    }
}
