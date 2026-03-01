<?php

declare(strict_types=1);

namespace App\Services;

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
            $path,
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
                            ($msg->sent_at?->format('Y-m-d H:i:s') ?? '') .
                            ($msg->text ?? '') .
                            ($msg->sender_name ?? '') .
                            ($msg->sender_external_id ?? '')
                        );
                });

            // Пул медиа-файлов из архива для сопоставления по порядку (WhatsApp и др., когда в экспорте нет имени файла)
            $absoluteExportPath = Storage::path($path);
            $mediaFallback      = $mediaRootPath !== null
                ? ['pool' => $this->collectMediaFilesFromRoot($mediaRootPath, $absoluteExportPath), 'index' => 0]
                : null;

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
                    $messageModelClass,
                    $mediaRootPath,
                    $messengerType,
                    $mediaFallback
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
     * casts модели. При переданном $mediaRootPath копирует медиа в хранилище и заполняет attachment_stored_path.
     * Если имени файла нет (WhatsApp и др.) — берёт следующий файл из пула по порядку.
     *
     * @param array<string, mixed>                             $message
     * @param class-string<Model>                              $messageModelClass
     * @param array{pool: array<int, string>, index: int}|null $mediaFallback
     *
     * @return array<string, mixed>
     */
    private function prepareMessageRowForInsert(
        array   $message,
        int     $conversationId,
        string  $messageModelClass,
        ?string $mediaRootPath = null,
        string  $messengerType = '',
        ?array  &$mediaFallback = null
    ): array {
        $attachmentStoredPath = null;
        if ($mediaRootPath !== null) {
            if (!empty($message['attachment_export_path'])) {
                $attachmentStoredPath = $this->copyMediaToStorage(
                    $mediaRootPath,
                    $message['attachment_export_path'],
                    $conversationId
                );
            }
            // Сопоставление по порядку: медиа-сообщение без имени файла — берём следующий файл из архива
            if ($attachmentStoredPath === null && $mediaFallback !== null && $this->isMediaMessageType($message['message_type'] ?? '')) {
                $pool = &$mediaFallback['pool'];
                $idx  = &$mediaFallback['index'];
                if (isset($pool[$idx])) {
                    $attachmentStoredPath = $this->copyFileByAbsolutePath($pool[$idx], $conversationId, $idx);
                    $idx++;
                }
            }
        }

        $text = $message['text'] ?? null;

        // Обрабатываем sent_at
        $sentAt = $message['sent_at'] ?? null;
        if ($sentAt instanceof Carbon) {
            $sentAt = $sentAt->format('Y-m-d H:i:s');
        }

        $row = array_merge($message, [
            'conversation_id'        => $conversationId,
            'sent_at'                => $sentAt,
            'text'                   => $text
                ? Crypt::encryptString($text)
                : null,
            'attachment_stored_path' => $attachmentStoredPath ?? ($message['attachment_stored_path'] ?? null),
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        if (isset($row['attachment_export_path'])) {
            $row['attachment_export_path'] = FilenameSanitizer::sanitize($row['attachment_export_path']);
        }
        if (isset($row['media_file'])) {
            $row['media_file'] = FilenameSanitizer::sanitize($row['media_file']);
        }

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
        $exportPath = str_replace(['\\', '../'], ['/', ''], $attachmentExportPath);
        $exportPath = ltrim($exportPath, '/');
        if ($exportPath === '') {
            return null;
        }

        $root           = rtrim($mediaRootPath, DIRECTORY_SEPARATOR);
        $sourceAbsolute = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $exportPath);

        if (!is_file($sourceAbsolute)) {
            $basename       = basename($exportPath);
            $sourceAbsolute = $this->findFileByBasename($root, $basename);
            if ($sourceAbsolute === null) {
                Log::debug('Import media file not found', ['path' => $exportPath, 'root' => $mediaRootPath]);

                return null;
            }
        }

        $safeSegment = FilenameSanitizer::sanitize($exportPath);
        if ($safeSegment === 'file') {
            $safeSegment = FilenameSanitizer::sanitize(basename($exportPath));
        }
        $storedRelative = sprintf('conversations/%d/media/%s', $conversationId, $safeSegment);
        $content        = file_get_contents($sourceAbsolute);
        if ($content === false) {
            return null;
        }
        Storage::put($storedRelative, $content);

        return $storedRelative;
    }

    /**
     * Рекурсивный поиск файла по имени в каталоге (учитывает разный регистр и вложенность).
     */
    private function findFileByBasename(string $dir, string $basename): ?string
    {
        if (!is_dir($dir)) {
            return null;
        }
        $items = @scandir($dir);
        if ($items === false) {
            return null;
        }
        $sep           = DIRECTORY_SEPARATOR;
        $basenameLower = strtolower($basename);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $dir . $sep . $item;
            if (is_file($full) && strtolower($item) === $basenameLower) {
                return $full;
            }
        }
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

    /**
     * Собирает список абсолютных путей ко всем файлам в каталоге (рекурсивно).
     * Исключает файл экспорта ($excludeAbsolutePath), чтобы не считать .txt/.json медиа.
     */
    private function collectMediaFilesFromRoot(string $dir, string $excludeAbsolutePath): array
    {
        $exclude = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $excludeAbsolutePath);
        $list    = [];
        $this->collectMediaFilesRecursive($dir, $exclude, $list);

        sort($list);

        return $list;
    }

    private function collectMediaFilesRecursive(string $dir, string $excludePath, array &$list): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = @scandir($dir);
        if ($items === false) {
            return;
        }
        $sep = DIRECTORY_SEPARATOR;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = rtrim($dir, $sep) . $sep . $item;
            if (is_file($full)) {
                $normalized = str_replace(['/', '\\'], $sep, $full);
                if ($normalized !== $excludePath) {
                    $list[] = $full;
                }
            } else {
                $this->collectMediaFilesRecursive($full, $excludePath, $list);
            }
        }
    }

    /**
     * Копирует один файл по абсолютному пути в Storage (conversations/{id}/media/...).
     * Имя в хранилище: по индексу и расширению оригинала, чтобы не перезаписывать.
     */
    private function copyFileByAbsolutePath(string $absolutePath, int $conversationId, int $index): ?string
    {
        if (!is_file($absolutePath)) {
            return null;
        }
        $ext            = pathinfo($absolutePath, PATHINFO_EXTENSION);
        $ext            = FilenameSanitizer::sanitize($ext);
        $name           = ($ext !== '' && $ext !== 'file')
            ? "media_{$index}.{$ext}"
            : "media_{$index}";
        $storedRelative = sprintf('conversations/%d/media/%s', $conversationId, $name);
        $content        = file_get_contents($absolutePath);
        if ($content === false) {
            return null;
        }
        Storage::put($storedRelative, $content);

        return $storedRelative;
    }

    private function isMediaMessageType(string $messageType): bool
    {
        $mediaTypes = ['photo', 'voice_message', 'video_message', 'media', 'sticker', 'document', 'gif', 'video'];

        return in_array(strtolower($messageType), $mediaTypes, true);
    }
}
