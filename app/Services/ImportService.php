<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\TelegramMessage;
use App\Models\WhatsAppMessage;
use App\Models\ViberMessage;
use App\Models\MessengerAccount;
use App\Services\Parsers\TelegramParser;
use Illuminate\Support\Facades\Crypt;
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
            default    => [null, []],
        };

        if (!$conversationData) {
            return;
        }

        DB::transaction(function () use ($userId, $messengerType, $conversationData, $messagesData) {
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

            /**
             * Автоматически находим или создаём переписку по external_id из экспорта.
             * Переписка ищется только среди переписок текущего пользователя (через messenger_account_id).
             */
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

            /**
             * Определяем модель сообщений в зависимости от типа мессенджера
             */
            $messageModel = match ($messengerType) {
                'telegram' => TelegramMessage::class,
                'whatsapp' => WhatsAppMessage::class,
                'viber' => ViberMessage::class,
                default => throw new \RuntimeException("Unknown messenger type: {$messengerType}"),
            };

            /**
             * Загружаем все существующие сообщения из БД
             */
            $messagesRelation = match ($messengerType) {
                'telegram' => $conversation->telegramMessages(),
                'whatsapp' => $conversation->whatsappMessages(),
                'viber' => $conversation->viberMessages(),
                default => throw new \RuntimeException("Unknown messenger type: {$messengerType}"),
            };

            $existingMessages = $messagesRelation
                ->get(['external_id', 'sent_at', 'text', 'sender_name', 'sender_external_id'])
                ->keyBy(function ($msg) {
                    /**
                     * Ключ для дедупликации: external_id или комбинация sent_at + text + sender
                     */
                    return $msg->external_id ?? md5(
                        ($msg->sent_at?->toIso8601String() ?? '') .
                        ($msg->text ?? '') .
                        ($msg->sender_name ?? '') .
                        ($msg->sender_external_id ?? '')
                    );
                });

            /**
             * Подготавливаем новые сообщения с ключами для дедупликации
             */
            $newMessagesToInsert = [];
            foreach ($messagesData as $message) {
                $key = $message['external_id'] ?? md5(
                    ($message['sent_at'] ?? '') .
                    ($message['text'] ?? '') .
                    ($message['sender_name'] ?? '') .
                    ($message['sender_external_id'] ?? '')
                );

                /**
                 * Пропускаем, если такое сообщение уже есть
                 */
                if ($existingMessages->has($key)) {
                    continue;
                }

                $text          = $message['text'] ?? null;
                $encryptedText = $text
                    ? Crypt::encryptString($text)
                    : null;

                // Базовые поля для всех типов сообщений
                $messageData = [
                    'conversation_id'    => $conversation->id,
                    'external_id'        => $message['external_id'] ?? null,
                    'sender_name'        => $message['sender_name'] ?? null,
                    'sender_external_id' => $message['sender_external_id'] ?? null,
                    'sent_at'            => $message['sent_at'] ?? null,
                    'text'               => $encryptedText,
                    'message_type'       => $message['message_type'] ?? 'text',
                    // insert() обходит касты модели, поэтому массив нужно сериализовать вручную
                    'raw'                => isset($message['raw'])
                        ? json_encode($message['raw'])
                        : null,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ];

                // Добавляем специфичные поля для Telegram
                if ($messengerType === 'telegram') {
                    $messageData = array_merge($messageData, [
                        'sticker_id' => $message['sticker_id'] ?? null,
                        'sticker_set_name' => $message['sticker_set_name'] ?? null,
                        'voice_duration' => $message['voice_duration'] ?? null,
                        'voice_file_id' => $message['voice_file_id'] ?? null,
                        'video_file_id' => $message['video_file_id'] ?? null,
                        'video_duration' => $message['video_duration'] ?? null,
                        'photo_file_id' => $message['photo_file_id'] ?? null,
                        'photo_sizes' => isset($message['photo_sizes']) ? json_encode($message['photo_sizes']) : null,
                        'service_action' => $message['service_action'] ?? null,
                        'service_actor' => isset($message['service_actor']) ? json_encode($message['service_actor']) : null,
                        'forwarded_from_chat_id' => $message['forwarded_from_chat_id'] ?? null,
                        'forwarded_from_message_id' => $message['forwarded_from_message_id'] ?? null,
                        'edited_at' => $message['edited_at'] ?? null,
                        'reactions' => isset($message['reactions']) ? json_encode($message['reactions']) : null,
                    ]);
                }

                // Добавляем специфичные поля для WhatsApp
                if ($messengerType === 'whatsapp') {
                    $messageData = array_merge($messageData, [
                        'status' => $message['status'] ?? null,
                        'is_forwarded' => $message['is_forwarded'] ?? false,
                        'voice_note_file_id' => $message['voice_note_file_id'] ?? null,
                        'media_file_id' => $message['media_file_id'] ?? null,
                        'reactions' => isset($message['reactions']) ? json_encode($message['reactions']) : null,
                        'labels' => isset($message['labels']) ? json_encode($message['labels']) : null,
                    ]);
                }

                // Добавляем специфичные поля для Viber
                if ($messengerType === 'viber') {
                    $messageData = array_merge($messageData, [
                        'media_url' => $message['media_url'] ?? null,
                        'sticker_id' => $message['sticker_id'] ?? null,
                        'urls' => isset($message['urls']) ? json_encode($message['urls']) : null,
                    ]);
                }

                $newMessagesToInsert[] = $messageData;
            }

            /**
             * Вставляем только новые сообщения в соответствующую таблицу
             */
            if ($newMessagesToInsert) {
                $messageModel::insert($newMessagesToInsert);
            }
        });
    }
}

