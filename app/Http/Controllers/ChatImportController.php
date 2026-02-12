<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessChatImport;
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
