<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MediaAttachment;
use App\Models\MediaTypes\SupportedMediaTypesEnum;
use App\Models\Message;
use App\Models\MessengerAccount;
use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
use App\Services\Import\DTO\MessageCreateResult;
use App\Services\Import\DTO\PreparedMessageRowResult;
use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\Media\MediaFileStorageService;
use App\Services\Parsers\ParserRegistry;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportService
{
    /**
     * @param ParserRegistry          $parserRegistry
     * @param MediaFileStorageService $mediaFileStorageService
     */
    public function __construct(
        protected ParserRegistry          $parserRegistry,
        protected MediaFileStorageService $mediaFileStorageService,
    ) {
    }

    /**
     * @param int                     $userId
     * @param string                  $messengerType
     * @param ImportStrategyInterface $strategy
     * @param ArchiveExtractionResult $extractedExportFile
     *
     * @throws Throwable
     * @return void
     */
    public function import(
        int                     $userId,
        string                  $messengerType,
        ImportStrategyInterface $strategy,
        ArchiveExtractionResult $extractedExportFile
    ): void {
        $exportFilePath = $extractedExportFile->getExportFileAbsolutePath();
        $mediaRootPath  = $extractedExportFile->getMediaRootPath();

        if ($exportFilePath === null) {
            return;
        }

        try {
            $parser               = $this->parserRegistry->get($messengerType);
            $importedConversation = $parser->parse($exportFilePath);
        } catch (Throwable $e) {
            Log::error('Import parsing failed', [
                'user_id'        => $userId,
                'messenger_type' => $messengerType,
                'path'           => $exportFilePath,
                'error'          => $e->getMessage(),
                'trace'          => $e->getTraceAsString(),
            ]);

            return;
        }

        if (!$importedConversation->hasConversation()) {
            Log::notice('Import skipped - no conversation data', [
                'user_id'        => $userId,
                'messenger_type' => $messengerType,
            ]);

            return;
        }

        $conversation = DB::transaction(function () use (
            $userId,
            $messengerType,
            $importedConversation,
            $strategy
        ) {
            $conversationData = $importedConversation->getConversationData();

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

            return $strategy->resolveConversation(
                account: $account,
                conversationData: $conversationData
            );
        });

        if (!$conversation) {
            Log::warning('Import aborted - no conversation target', [
                'mode'    => $strategy->getName(),
                'user_id' => $userId,
            ]);

            return;
        }

        $messagesRelation    = $parser->getMessagesRelation($conversation);
        $existingExternalIds = $messagesRelation
            ->whereNotNull('external_id')
            ->pluck('external_id')
            ->map(static fn ($id): string => (string)$id)
            ->flip();
        $existingDedupHashes = $messagesRelation
            ->whereNotNull('dedup_hash')
            ->pluck('dedup_hash')
            ->map(static fn ($hash): string => (string)$hash)
            ->flip();

        $preparedMessages  = [];
        $copiedMediaPaths  = [];
        $messageModelClass = $parser->getMessageModelClass();
        foreach ($importedConversation->getMessages() as $message) {
            $externalId = $this->normalizeExternalId($message['external_id'] ?? null);
            $dedupHash  = $this->buildDeduplicationHash($message);

            if ($externalId !== null && $existingExternalIds->has($externalId)) {
                continue;
            }
            if ($existingDedupHashes->has($dedupHash)) {
                continue;
            }

            if ($externalId !== null) {
                $existingExternalIds->put($externalId, true);
            }
            $existingDedupHashes->put($dedupHash, true);

            $attachmentStoredPath = null;
            if ($mediaRootPath !== null && !empty($message['attachment_export_path'])) {
                $attachmentStoredPath = $this->mediaFileStorageService->copyForConversation(
                    $mediaRootPath,
                    (string)$message['attachment_export_path'],
                    $conversation->id
                );
                if ($attachmentStoredPath !== null) {
                    $copiedMediaPaths[$attachmentStoredPath] = true;
                }
            }

            $message['dedup_hash'] = $dedupHash;

            $preparedMessages[] = $this->prepareMessageRowForInsert(
                $message,
                $conversation->id,
                $messageModelClass,
                $attachmentStoredPath,
            );
        }

        $importedCount = 0;
        try {
            DB::transaction(function () use ($messageModelClass, $preparedMessages, $conversation, &$importedCount) {
                /**
                 * @var PreparedMessageRowResult $prepared
                 */
                foreach ($preparedMessages as $prepared) {
                    $insertResult = $this->createMessageSafely($messageModelClass, $prepared->getRow());
                    $msg          = $insertResult->getMessage();
                    if (!$insertResult->isCreated()) {
                        continue;
                    }

                    $media = $prepared->getMedia();
                    if (isset($media)) {
                        $media = MediaAttachment::create(array_merge($media, [
                            'conversation_id' => $conversation->id,
                        ]));
                        $msg->update(['media_attachment_id' => $media->id]);
                    }

                    $importedCount++;
                }
            });
        } catch (Throwable $e) {
            foreach (array_keys($copiedMediaPaths) as $path) {
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }

            throw $e;
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
    }

    /**
     * Собирает атрибуты для Model::create и опционально данные для MediaAttachment.
     *
     * @param array<string, mixed>  $message
     * @param int                   $conversationId
     * @param class-string<Message> $messageModelClass
     * @param string|null           $attachmentStoredPath
     *
     * @return PreparedMessageRowResult
     */
    private function prepareMessageRowForInsert(
        array   $message,
        int     $conversationId,
        string  $messageModelClass,
        ?string $attachmentStoredPath = null
    ): PreparedMessageRowResult {
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
                'media_type'        => SupportedMediaTypesEnum::detect($mime, $exportNormalized)?->value,
                'mime_type'         => $mime,
                'original_filename' => $exportNormalized
                    ? basename(str_replace('\\', '/', $exportNormalized))
                    : ($attachmentStoredPath
                        ? basename($attachmentStoredPath)
                        : null),
            ];
        }

        return new PreparedMessageRowResult($row, $mediaPayload);
    }

    /**
     * @param class-string<Message> $messageModelClass
     * @param array<string, mixed>  $row
     *
     * @throws QueryException
     * @return MessageCreateResult
     */
    private function createMessageSafely(string $messageModelClass, array $row): MessageCreateResult
    {
        try {
            /** @var Model $message */
            $message = $messageModelClass::create($row);

            return new MessageCreateResult($message, true);
        } catch (QueryException $e) {
            if (!$this->isUniqueConstraintViolation($e)) {
                throw $e;
            }

            /** @var Model|null $existing */
            $existing = $messageModelClass::query()
                ->where('conversation_id', $row['conversation_id'] ?? null)
                ->where(function ($q) use ($row): void {
                    $externalId = $this->normalizeExternalId($row['external_id'] ?? null);
                    if ($externalId !== null) {
                        $q->where('external_id', $externalId);
                    } else {
                        $q->where('dedup_hash', $row['dedup_hash'] ?? null);
                    }
                })
                ->first();

            if ($existing === null) {
                throw $e;
            }

            return new MessageCreateResult($existing, false);
        }
    }

    /**
     * @param QueryException $e
     *
     * @return bool
     */
    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'duplicate')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'unique violation');
    }

    /**
     * @param mixed $externalId
     *
     * @return string|null
     */
    private function normalizeExternalId(mixed $externalId): ?string
    {
        if (!is_scalar($externalId)) {
            return null;
        }

        $normalized = trim((string)$externalId);

        return $normalized !== ''
            ? $normalized
            : null;
    }

    /**
     * @param array<string, mixed> $message
     *
     * @return string
     */
    private function buildDeduplicationHash(array $message): string
    {
        $sentAt = $message['sent_at'] ?? '';
        if ($sentAt instanceof Carbon) {
            $sentAtFormatted = $sentAt->format('Y-m-d H:i:s');
        } elseif (is_string($sentAt)) {
            try {
                $sentAtFormatted = Carbon::parse($sentAt)->format('Y-m-d H:i:s');
            } catch (Exception) {
                $sentAtFormatted = $sentAt;
            }
        } else {
            $sentAtFormatted = (string)$sentAt;
        }

        return hash(
            'sha256',
            $sentAtFormatted .
            ($message['text'] ?? '') .
            ($message['sender_name'] ?? '') .
            ($message['sender_external_id'] ?? '')
        );
    }

    /**
     * @param mixed $exportRaw
     *
     * @return string|null
     */
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
}
