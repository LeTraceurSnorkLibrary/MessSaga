<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $messengerType = $request->string('messenger')->toString();

        $query = Conversation::query()
            ->whereHas('messengerAccount', function (Builder $q) use ($request, $messengerType) {
                $q->where('user_id', $request->user()->id);

                if ($messengerType) {
                    $q->where('type', $messengerType);
                }
            })
            ->with(['messages' => fn ($q) => $q->latest('sent_at')->limit(1)]);

        $conversations = $query
            ->orderByDesc('id')
            ->get()
            ->map(function (Conversation $conversation) {
                $last = $conversation->messages->first();

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'preview' => $last?->text,
                ];
            });

        return response()->json($conversations);
    }

    public function messages(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, 403);

        $messages = $conversation->messages()
            ->orderBy('sent_at')
            ->get([
                'id',
                'sender_name',
                'sent_at',
                'text',
            ]);

        return response()->json($messages);
    }

    public function destroy(Request $request, Conversation $conversation)
    {
        abort_unless($conversation->messengerAccount->user_id === $request->user()->id, 403);

        $conversation->delete(); // каскадно удалит сообщения за счёт foreign key

        return response()->noContent();
    }
}
