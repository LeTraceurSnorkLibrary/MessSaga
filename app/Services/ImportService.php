<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Parsers\ParserRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportService
{
    /**
     * @param ParserRegistry $parserRegistry
     */
    public function __construct(
        protected ParserRegistry $parserRegistry,
    ) {
    }

    /**
     * @param int    $userId
     * @param string $messengerType
     * @param string $path
     *
     * @return void
     */
    public function import(int $userId, string $messengerType, string $path): void
    {
        $absolutePath = Storage::path($path);

        $parser = $this->parserRegistry->get($messengerType);
        $dto    = $parser->parse($absolutePath);

        if (!$dto->hasConversation()) {
            return;
        }

        $messageModelClass = $parser->getMessageModelClass();

        DB::transaction(function () use ($userId, $messengerType, $dto, $messageModelClass) {
            $conversationData = $dto->getConversationData();

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

            $messagesRelation = $conversation->messagesQuery();
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
            foreach ($dto->getMessages() as $message) {
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

                $row                   = $this->prepareMessageRowForInsert(
                    $message,
                    $conversation->id,
                    $messageModelClass
                );
                $newMessagesToInsert[] = $row;
            }

            if ($newMessagesToInsert) {
                $messageModelClass::insert($newMessagesToInsert);
            }
        });
    }

    /**
     * Собирает строку для insert: добавляет conversation_id, шифрует text, timestamps, кодирует array/json-поля по
     * casts модели.
     *
     * @param array<string, mixed> $message
     * @param class-string<Model>  $messageModelClass
     *
     * @return array<string, mixed>
     */
    private function prepareMessageRowForInsert(array $message, int $conversationId, string $messageModelClass): array
    {
        $text = $message['text'] ?? null;
        $row  = array_merge($message, [
            'conversation_id' => $conversationId,
            'text'            => $text
                ? Crypt::encryptString($text)
                : null,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        /**
         * @var Model $model
         */
        $model   = $messageModelClass::make();
        $casts   = $model->getCasts();
        $allowed = array_merge($model->getFillable(), ['created_at', 'updated_at']);
        // Нормализуем строку: у всех строк один и тот же набор ключей в одном порядке (иначе bulk insert падает)
        $row = array_merge(
            array_fill_keys($allowed, null),
            array_intersect_key($row, array_flip($allowed))
        );

        foreach ($row as $key => $value) {
            if (!isset($casts[$key])) {
                continue;
            }
            $cast = $casts[$key];
            if (($cast === 'array' || $cast === 'json') && is_array($value)) {
                $row[$key] = json_encode($value);
            }
        }

        return $row;
    }
}
