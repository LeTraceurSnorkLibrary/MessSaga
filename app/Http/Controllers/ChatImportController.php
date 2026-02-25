<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\ProcessChatImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatImportController extends Controller
{
    /**
     * Переписка определяется автоматически по external_id из файла экспорта.
     * Если переписка не существует - создаётся новая, если существует - догружаются сообщения.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'messenger_type' => 'required|string|in:telegram,whatsapp,viber',
            'file'           => 'required|file',
        ]);

        $path = $request->file('file')->store('chat_imports');

        ProcessChatImport::dispatch(
            userId: $request->user()->id,
            messengerType: $data['messenger_type'],
            path: $path,
        );

        return response()->json([
            'status' => 'queued',
        ]);
    }
}
