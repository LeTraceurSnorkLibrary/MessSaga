<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Models\Message;
use App\Services\Parsers\TelegramParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    public function __construct(
        protected TelegramParser $telegramParser,
    ) {
    }

    public function import(int $userId, string $messengerType, string $path): void
    {
        $absolutePath = Storage::path($path);

        [$conversationData, $messagesData] = match ($messengerType) {
            'telegram' => $this->telegramParser->parse($absolutePath),
            default => [null, []],
        };

        if (! $conversationData) {
            return;
        }

        DB::transaction(function () use ($userId, $messengerType, $conversationData, $messagesData) {
            $account = MessengerAccount::firstOrCreate(
                [
                    'user_id' => $userId,
                    'type' => $messengerType,
                ],
                [
                    'name' => $conversationData['account_name'] ?? ucfirst($messengerType),
                    'meta' => $conversationData['account_meta'] ?? [],
                ],
            );

            $conversation = Conversation::updateOrCreate(
                [
                    'messenger_account_id' => $account->id,
                    'external_id' => $conversationData['external_id'] ?? null,
                ],
                [
                    'title' => $conversationData['title'] ?? 'Unknown chat',
                    'participants' => $conversationData['participants'] ?? [],
                ],
            );

            foreach ($messagesData as $message) {
                $conversation->messages()->create([
                    'external_id' => $message['external_id'] ?? null,
                    'sender_name' => $message['sender_name'] ?? null,
                    'sender_external_id' => $message['sender_external_id'] ?? null,
                    'sent_at' => $message['sent_at'] ?? null,
                    'text' => $message['text'] ?? null,
                    'raw' => $message['raw'] ?? null,
                ]);
            }
        });
    }
}

