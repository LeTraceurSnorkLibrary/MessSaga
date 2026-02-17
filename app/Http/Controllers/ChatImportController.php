<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessChatImport;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatImportController extends Controller
{
    /**
     * Handle chat import upload.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'messenger_type' => 'required|string|in:telegram,whatsapp,viber',
            'file' => 'required|file',
            'conversation_id' => 'nullable|integer|exists:conversations,id',
        ]);

        // Проверяем, что conversation_id принадлежит текущему пользователю
        if ($data['conversation_id'] ?? null) {
            $conversation = Conversation::findOrFail($data['conversation_id']);
            abort_unless(
                $conversation->messengerAccount->user_id === $request->user()->id,
                403,
                'Conversation does not belong to you'
            );
        }

        $path = $request->file('file')->store('chat_imports');

        ProcessChatImport::dispatch(
            userId: $request->user()->id,
            messengerType: $data['messenger_type'],
            path: $path,
            conversationId: $data['conversation_id'] ?? null,
        );

        return response()->json([
            'status' => 'queued',
        ]);
    }
}
