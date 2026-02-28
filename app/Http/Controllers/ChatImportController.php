<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessChatImport;
use App\Services\Import\DTO\ImportModeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Teapot\StatusCode\WebDAV;

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
            'file'                   => 'required|file|max:102400', // 100MB max
            'import_mode'            => 'required|string|in:auto,new,select',
            'target_conversation_id' => 'nullable|integer|exists:conversations,id',
        ]);

        /**
         * ID must be present for "select" mode
         */
        if ($data['import_mode'] === ImportModeEnum::SELECT->value && empty($data['target_conversation_id'])) {
            return response()->json([
                'error' => 'Для режима "select" необходимо указать ID переписки.',
            ], WebDAV::UNPROCESSABLE_ENTITY);
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
            'status'  => 'queued',
            'message' => 'Импорт поставлен в очередь',
        ]);
    }
}
