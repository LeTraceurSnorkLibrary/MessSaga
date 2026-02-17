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

    public function import(int $userId, string $messengerType, string $path, ?int $conversationId = null): void
    {
        $absolutePath = Storage::path($path);

        [$conversationData, $messagesData] = match ($messengerType) {
            'telegram' => $this->telegramParser->parse($absolutePath),
            default    => [null, []],
        };

        if (!$conversationData) {
            return;
        }

        DB::transaction(function () use ($userId, $messengerType, $conversationData, $messagesData, $conversationId) {
            $account = MessengerAccount::firstOrCreate(
                [
                    'user_id' => $userId,
                    'type'    => $messengerType,
                ],
                [
                    'name' => $conversationData['account_name'] ?? ucfirst($messengerType),
                    'meta' => $conversationData['account_meta'] ?? [],
                ],
            );

            // Если передан conversationId, используем существующую переписку
            if ($conversationId) {
                $conversation = Conversation::where('id', $conversationId)
                    ->where('messenger_account_id', $account->id)
                    ->firstOrFail();
            } else {
                // Иначе создаём/находим переписку как раньше
                $conversation = Conversation::updateOrCreate(
                    [
                        'messenger_account_id' => $account->id,
                        'external_id'          => $conversationData['external_id'] ?? null,
                    ],
                    [
                        'title'        => $conversationData['title'] ?? 'Unknown chat',
                        'participants' => $conversationData['participants'] ?? [],
                    ],
                );
            }

            // Загружаем все существующие сообщения из БД
            $existingMessages = $conversation->messages()
                ->get(['external_id', 'sent_at', 'text', 'sender_name', 'sender_external_id'])
                ->keyBy(function ($msg) {
                    // Ключ для дедупликации: external_id или комбинация sent_at + text + sender
                    return $msg->external_id ?? md5(
                        ($msg->sent_at?->toIso8601String() ?? '') .
                        ($msg->text ?? '') .
                        ($msg->sender_name ?? '') .
                        ($msg->sender_external_id ?? '')
                    );
                });

            // Подготавливаем новые сообщения с ключами для дедупликации
            $newMessagesToInsert = [];
            foreach ($messagesData as $message) {
                $key = $message['external_id'] ?? md5(
                    ($message['sent_at'] ?? '') .
                    ($message['text'] ?? '') .
                    ($message['sender_name'] ?? '') .
                    ($message['sender_external_id'] ?? '')
                );

                // Пропускаем, если такое сообщение уже есть
                if ($existingMessages->has($key)) {
                    continue;
                }

                $newMessagesToInsert[] = [
                    'conversation_id'    => $conversation->id,
                    'external_id'        => $message['external_id'] ?? null,
                    'sender_name'        => $message['sender_name'] ?? null,
                    'sender_external_id' => $message['sender_external_id'] ?? null,
                    'sent_at'            => $message['sent_at'] ?? null,
                    'text'               => $message['text'] ?? null,
                    // insert() обходит касты модели, поэтому массив нужно сериализовать вручную
                    'raw'                => isset($message['raw'])
                        ? json_encode($message['raw'])
                        : null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];
            }

            // Вставляем только новые сообщения
            if ($newMessagesToInsert) {
                Message::insert($newMessagesToInsert);
            }
        });
    }
}

