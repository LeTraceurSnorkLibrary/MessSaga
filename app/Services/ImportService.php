<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MediaAttachment;
use App\Models\MessengerAccount;
use App\Services\Import\ImportStrategyFactory;
use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\Parsers\ParserRegistry;
use App\Support\FilenameSanitizer;
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
     * @param int                     $userId
     * @param string                  $messengerType
     * @param string                  $path
     * @param ImportStrategyInterface $strategy
     * @param string|null             $mediaRootPath Абсолютный путь к корню распакованного архива с медиа (если импорт
     *                                               из ZIP)
     *
     */
    public function import(
        int                     $userId,
        string                  $messengerType,
        string                  $path,
        ImportStrategyInterface $strategy,
        ?string                 $mediaRootPath = null
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

        DB::transaction(function () use (
            $userId,
            $messengerType,
            $dto,
            $messageModelClass,
            $parser,
            $strategy,
            $mediaRootPath,
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

            $conversation = $strategy->resolveConversation(
                account: $account,
                conversationData: $conversationData
            );

            if (!$conversation) {
                Log::warning('Import aborted - no conversation target', [
                    'mode'    => $strategy->getName(),
                    'user_id' => $userId,
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
                        ($msg->sent_at?->format('Y-m-d H:i:s') ?? '')
                            . ($msg->text ?? '')
                            . ($msg->sender_name ?? '')
                            . ($msg->sender_external_id ?? '')
                    );
                });

            /**
             * Подготовка и сохранение новых сообщений (по одному — чтобы привязать MediaAttachment).
             */
            $importedCount = 0;
            foreach ($dto->getMessages() as $message) {
                if (!empty($message['external_id'])) {
                    $key = 'ID:' . $message['external_id'];
                } else {
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
                        $sentAtFormatted
                            . ($message['text'] ?? '')
                            . ($message['sender_name'] ?? '')
                            . ($message['sender_external_id'] ?? '')
                    );
                }

                if ($existingMessages->has($key)) {
                    continue;
                }

                $prepared = $this->prepareMessageRowForInsert(
                    $message,
                    $conversation->id,
                    $messageModelClass,
                    $mediaRootPath,
                );

                /** @var Model $msg */
                $msg = $messageModelClass::create($prepared['row']);

                if ($prepared['media'] !== null) {
                    $media = MediaAttachment::create(array_merge($prepared['media'], [
                        'conversation_id' => $conversation->id,
                    ]));
                    $msg->update(['media_attachment_id' => $media->id]);
                }

                $importedCount++;
            }

            if ($importedCount > 0) {
                Log::info('Messages imported', [
                    'conversation_id' => $conversation->id,
                    'count'           => $importedCount,
                ]);
            } else {
                Log::info('No new messages to import', [
                    'conversation_id' => $conversation->id,
                ]);
            }
        });
    }

    /**
     * Собирает атрибуты для Model::create и опционально данные для MediaAttachment.
     *
     * @param array<string, mixed> $message
     * @param class-string<Model>  $messageModelClass
     *
     * @return array{row: array<string, mixed>, media: ?array<string, mixed>}
     */
    private function prepareMessageRowForInsert(
        array   $message,
        int     $conversationId,
        string  $messageModelClass,
        ?string $mediaRootPath = null
    ): array {
        $attachmentStoredPath = null;
        if ($mediaRootPath !== null && !empty($message['attachment_export_path'])) {
            $attachmentStoredPath = $this->copyMediaToStorage(
                $mediaRootPath,
                $message['attachment_export_path'],
                $conversationId
            );
        }

        $exportRaw        = $message['attachment_export_path'] ?? null;
        $exportNormalized = $this->normalizeExportPath($exportRaw);

        unset($message['attachment_export_path'], $message['attachment_stored_path']);

        $text = $message['text'] ?? null;

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
        ]);

        if (isset($row['media_file'])) {
            $row['media_file'] = FilenameSanitizer::sanitize($row['media_file']);
        }

        /**
         * @var Model $model
         */
        $model   = $messageModelClass::make();
        $allowed = $model->getFillable();
        $row     = array_merge(
            array_fill_keys($allowed, null),
            array_intersect_key($row, array_flip($allowed))
        );

        $mediaPayload = null;
        if ($exportNormalized !== null || $attachmentStoredPath !== null) {
            $mime = null;
            if ($attachmentStoredPath !== null && Storage::exists($attachmentStoredPath)) {
                $mime = Storage::mimeType($attachmentStoredPath);
            }
            $mediaPayload = [
                'stored_path'       => $attachmentStoredPath,
                'export_path'       => $exportNormalized,
                'mime_type'         => $mime,
                'original_filename' => $exportNormalized
                    ? basename(str_replace('\\', '/', $exportNormalized))
                    : ($attachmentStoredPath
                        ? basename($attachmentStoredPath)
                        : null),
            ];
        }

        return [
            'row'   => $row,
            'media' => $mediaPayload,
        ];
    }

    /**
     * Копирует файл медиа из корня архива в хранилище (conversations/{id}/media/...).
     * Сначала ищет по точному пути из экспорта, затем по имени файла в любом подкаталоге.
     * Возвращает относительный путь в Storage или null, если файл не найден.
     */
    private function copyMediaToStorage(
        string $mediaRootPath,
        string $attachmentExportPath,
        int    $conversationId
    ): ?string {
        $root           = rtrim($mediaRootPath, DIRECTORY_SEPARATOR);
        $sourceAbsolute = null;
        $basename       = null;
        foreach ($this->extractCandidateBasenames($attachmentExportPath) as $candidate) {
            $sanitizedCandidate = FilenameSanitizer::sanitize($candidate);
            if ($sanitizedCandidate === 'file') {
                continue;
            }
            $found = $this->findFileByBasename($root, $sanitizedCandidate);
            if ($found !== null) {
                $sourceAbsolute = $found;
                $basename       = $sanitizedCandidate;
                break;
            }
        }
        if ($sourceAbsolute === null) {
            Log::debug('Import media file not found', [
                'export_path' => $attachmentExportPath,
                'basename'    => basename(str_replace('\\', '/', $attachmentExportPath)),
                'root'        => $mediaRootPath,
            ]);

            return null;
        }

        $safeSegment    = $basename;
        $storedRelative = sprintf('conversations/%d/media/%s', $conversationId, $safeSegment);
        $content        = file_get_contents($sourceAbsolute);
        if ($content === false) {
            return null;
        }
        Storage::put($storedRelative, $content);

        return $storedRelative;
    }

    private function normalizeExportPath(mixed $exportRaw): ?string
    {
        if (!is_string($exportRaw)) {
            return null;
        }

        $normalized = trim(str_replace('\\', '/', $exportRaw));
        if ($normalized === '') {
            return null;
        }

        return $normalized;
    }

    /**
     * Старые данные могли сохранить export_path как "photos_photo_1.jpg" (без слешей).
     * Пробуем как обычный basename, так и "хвосты" после "_" для обратной совместимости.
     *
     * @return array<int, string>
     */
    private function extractCandidateBasenames(string $attachmentExportPath): array
    {
        $normalized = str_replace('\\', '/', $attachmentExportPath);
        $basename   = basename($normalized);
        $candidates = [$basename];

        if (!str_contains($normalized, '/')) {
            $parts = explode('_', $normalized);
            for ($i = 1; $i < count($parts); $i++) {
                $suffix = implode('_', array_slice($parts, $i));
                if ($suffix !== '' && str_contains($suffix, '.')) {
                    $candidates[] = $suffix;
                }
            }
        }

        return array_values(array_unique($candidates));
    }

    /**
     * Рекурсивный поиск файла по имени в каталоге.
     * Сопоставление идёт по имени, очищенному тем же FilenameSanitizer, что и attachment_export_path,
     * так что различия только в невидимых символах / регистре не мешают найти файл.
     */
    private function findFileByBasename(string $dir, string $basename): ?string
    {
        if (!is_dir($dir)) {
            return null;
        }

        $target = strtolower(FilenameSanitizer::sanitize($basename));
        if ($target === '' || $target === 'file') {
            return null;
        }

        $items = @scandir($dir);
        if ($items === false) {
            return null;
        }

        $sep = DIRECTORY_SEPARATOR;

        // Сначала ищем в текущем каталоге
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $dir . $sep . $item;
            if (is_file($full)) {
                $sanitized = strtolower(FilenameSanitizer::sanitize($item));
                if ($sanitized === $target) {
                    return $full;
                }
            }
        }

        // Затем спускаемся в подпапки
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $dir . $sep . $item;
            if (is_dir($full)) {
                $found = $this->findFileByBasename($full, $basename);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
