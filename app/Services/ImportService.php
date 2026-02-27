<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\MessengerAccount;
use App\Services\Parsers\ParserRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
     * @param int      $userId
     * @param string   $messengerType
     * @param string   $path
     * @param string   $mode auto|new|select
     * @param int|null $targetConversationId
     *
     * @throws Throwable
     */
    public function import(
        int $userId,
        string $messengerType,
        string $path,
        string $mode = 'auto',
        ?int $targetConversationId = null
    ): void {
        $absolutePath = Storage::path($path);

        try {
            $parser = $this->parserRegistry->get($messengerType);
            $dto    = $parser->parse($absolutePath);
        } catch (Throwable $e) {
            Log::error('Import parsing failed', [
                'user_id'        => $userId,
                'messenger_type' => $messengerType,
                'path'           => $path,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);

            return;
        }

        if (!$dto->hasConversation()) {
            Log::notice('Import skipped - no conversation data', [
                'user_id'        => $userId,
                'messenger_type' => $messengerType,
            ]);

            return;
        }

        $messageModelClass = $parser->getMessageModelClass();

        DB::transaction(
            function () use ($userId, $messengerType, $dto, $messageModelClass, $parser, $mode, $targetConversationId) {
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

                // Определяем целевую переписку на основе режима
                $conversation = $this->resolveConversation(
                    mode: $mode,
                    targetConversationId: $targetConversationId,
                    accountId: $account->id,
                    externalId: (string)($conversationData['external_id'] ?? null),
                    conversationData: $conversationData,
                    userId: $userId,
                );

                if (!$conversation) {
                    Log::warning('Import aborted - no conversation target', [
                        'mode'      => $mode,
                        'target_id' => $targetConversationId,
                        'user_id'   => $userId,
                    ]);

                    return;
                }

                /**
                 * Используем relation из парсера, а не из модели
                 */
                $messagesRelation = $parser->getMessagesRelation($conversation);

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

                    Log::info('Messages imported', [
                        'conversation_id' => $conversation->id,
                        'count'           => count($newMessagesToInsert),
                    ]);
                }
            }
        );
    }

    /**
     * Определяет целевую переписку на основе режима.
     *
     * @param string      $mode
     * @param int|null    $targetConversationId
     * @param int         $accountId
     * @param string|null $externalId
     * @param array       $conversationData
     * @param int         $userId
     *
     * @return Conversation|null
     */
    private function resolveConversation(
        string $mode,
        ?int $targetConversationId,
        int $accountId,
        ?string $externalId,
        array $conversationData,
        int $userId
    ): ?Conversation {
        if ($mode === 'new') {
            // Принудительно создаём новую переписку
            return Conversation::create([
                'messenger_account_id' => $accountId,
                'external_id'          => $externalId, // может быть null, но ок
                'title'                => $conversationData['title'] ?? 'Unknown chat',
                'participants'         => $conversationData['participants'] ?? [],
            ]);
        }

        if ($mode === 'select') {
            // Используем указанную переписку, проверяем принадлежность (уже проверено в контроллере, но для надёжности)
            $conversation = Conversation::where('id', $targetConversationId)
                ->whereHas('messengerAccount', fn ($q) => $q->where('user_id', $userId))
                ->first();

            if (!$conversation) {
                Log::warning('Selected conversation not found or not owned', [
                    'target_id' => $targetConversationId,
                    'user_id'   => $userId,
                ]);

                return null;
            }

            return $conversation;
        }

        // Режим 'auto' - стандартное поведение
        return Conversation::updateOrCreate(
            [
                'messenger_account_id' => $accountId,
                'external_id'          => $externalId,
            ],
            [
                'title'        => $conversationData['title'] ?? 'Unknown chat',
                'participants' => $conversationData['participants'] ?? [],
            ]
        );
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
