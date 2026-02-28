<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MessengerAccount;
use App\Services\Import\DTO\ImportModeDTO;
use App\Services\Import\ImportStrategyFactory;
use App\Services\Import\Strategies\AutoImportStrategy;
use App\Services\Parsers\ParserRegistry;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportService
{
    /**
     * @param ParserRegistry        $parserRegistry
     * @param ImportStrategyFactory $strategyFactory
     */
    public function __construct(
        protected ParserRegistry        $parserRegistry,
        protected ImportStrategyFactory $strategyFactory,
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
        int    $userId,
        string $messengerType,
        string $path,
        string $mode = AutoImportStrategy::IMPORT_STRATEGY_NAME,
        ?int   $targetConversationId = null
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
        $importStrategy    = $this->strategyFactory->getStrategy($mode);
        $importModeDTO     = ImportModeDTO::fromRequest($mode, $targetConversationId);

        DB::transaction(function () use (
            $userId,
            $messengerType,
            $dto,
            $messageModelClass,
            $parser,
            $importStrategy,
            $importModeDTO
        ) {
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

            $conversation = $importStrategy->resolveConversation(
                account: $account,
                conversationData: $conversationData,
                userId: $userId,
                mode: $importModeDTO
            );

            if (!$conversation) {
                Log::warning('Import aborted - no conversation target', [
                    'mode'      => $importStrategy->getName(),
                    'target_id' => $importModeDTO->targetConversationId,
                    'user_id'   => $userId,
                ]);

                return;
            }

            /**
             * Используем relation из парсера, а не из модели
             */
            $messagesRelation = $parser->getMessagesRelation($conversation);

            /**
             * Load existing messages for deduplication
             */
            $existingMessages = $messagesRelation
                ->get(['external_id', 'sent_at', 'text', 'sender_name', 'sender_external_id'])
                ->keyBy(function ($msg) {
                    /**
                     * Deduplicaton key: external_id, if present
                     */
                    if ($msg->external_id) {
                        return 'ID:' . $msg->external_id;
                    }

                    /**
                     * Deduplicaton key: combination of sent_at + text + sender
                     */
                    return 'hash:' . md5(
                            ($msg->sent_at?->format('Y-m-d H:i:s') ?? '') .
                            ($msg->text ?? '') .
                            ($msg->sender_name ?? '') .
                            ($msg->sender_external_id ?? '')
                        );
                });

            /**
             * Preparing new messages with deduplication keys
             */
            $newMessagesToInsert = [];
            foreach ($dto->getMessages() as $message) {
                /**
                 * Prepare a key for deduplication
                 */
                if (!empty($message['external_id'])) {
                    $key = 'ID:' . $message['external_id'];
                } else {
                    /**
                     * Format sent_at field
                     */
                    $sentAt = $message['sent_at'] ?? '';
                    if ($sentAt instanceof Carbon) {
                        $sentAtFormatted = $sentAt->format('Y-m-d H:i:s');
                    } elseif (is_string($sentAt)) {
                        try {
                            $sentAtFormatted = Carbon::parse($sentAt)->format('Y-m-d H:i:s');
                        } catch (Exception $e) {
                            $sentAtFormatted = $sentAt;
                        }
                    } else {
                        $sentAtFormatted = (string)$sentAt;
                    }

                    $key = 'hash:' . md5(
                            $sentAtFormatted .
                            ($message['text'] ?? '') .
                            ($message['sender_name'] ?? '') .
                            ($message['sender_external_id'] ?? '')
                        );
                }

                /**
                 * Skip message if already exist
                 */
                if ($existingMessages->has($key)) {
                    continue;
                }

                $newMessagesToInsert[] = $this->prepareMessageRowForInsert(
                    $message,
                    $conversation->id,
                    $messageModelClass
                );
            }

            if (count($newMessagesToInsert) > 0) {
                $messageModelClass::insert($newMessagesToInsert);

                Log::info('Messages imported', [
                    'conversation_id' => $conversation->id,
                    'count'           => count($newMessagesToInsert),
                ]);
            } else {
                Log::info('No new messages to import', [
                    'conversation_id' => $conversation->id,
                ]);
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

        // Обрабатываем sent_at
        $sentAt = $message['sent_at'] ?? null;
        if ($sentAt instanceof Carbon) {
            $sentAt = $sentAt->format('Y-m-d H:i:s');
        }

        $row = array_merge($message, [
            'conversation_id' => $conversationId,
            'sent_at'         => $sentAt,
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
