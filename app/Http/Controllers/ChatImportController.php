<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessChatImport;
use App\Services\Import\DTO\ImportModeDTO;
use App\Services\Import\ImportStrategyFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $path = $request->file('file')->store('chat_imports');

        /**
         * @var string $import_mode
         */
        $import_mode = $data['import_mode'];
        /**
         * @var int $requestUserId
         */
        $requestUserId        = $request->user()->id;
        $targetConversationId = isset($data['target_conversation_id'])
            ? (int)$data['target_conversation_id']
            : null;
        $importModeDTO        = new ImportModeDTO($import_mode, $targetConversationId);
        $strategy             = (new ImportStrategyFactory())
            ->forUserId($requestUserId)
            ->getStrategy($importModeDTO);

        ProcessChatImport::dispatch(
            userId: $requestUserId,
            messengerType: $data['messenger_type'],
            path: $path,
            strategy: $strategy,
        );

        return response()->json([
            'status'  => 'queued',
            'message' => 'Импорт поставлен в очередь',
        ]);
    }
}
