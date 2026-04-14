<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MediaAttachment;
use App\Models\MessengerAccount;
use App\Models\User;
use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
use App\Services\Import\DTO\PreparedMessageRowResult;
use App\Services\Import\MessageInsertService;
use App\Services\Import\MessagePreparationService;
use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\Media\Storage\MediaStorageInterface;
use App\Services\Parsers\ParserRegistry;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class ImportService
{
    /**
     * @param ParserRegistry            $parserRegistry
     * @param MessagePreparationService $messagePreparationService
     * @param MessageInsertService      $messageInsertService
     * @param MediaStorageInterface     $mediaStorage
     */
    public function __construct(
        protected ParserRegistry            $parserRegistry,
        protected MessagePreparationService $messagePreparationService,
        protected MessageInsertService      $messageInsertService,
        protected MediaStorageInterface     $mediaStorage,
    ) {
    }

    /**
     * @param int                     $userId
     * @param string                  $messengerType
     * @param ImportStrategyInterface $strategy
     * @param ArchiveExtractionResult $extractedExportFile
     *
     * @throws QueryException
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
        } catch (RuntimeException|InvalidArgumentException $e) {
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

        $user             = User::query()->find($userId);
        $tariffAllowsMedia = $user?->tariff()->allowsMediaUpload() ?? false;
        $canUploadMedia   = $user?->canUploadMedia() ?? false;
        $usedStorageBytes = $user?->getUsedMediaStorageBytes() ?? 0;
        $usedMediaFiles   = $user?->getUsedMediaFilesCount() ?? 0;

        $messagesRelation    = $parser->getMessagesRelation($conversation);
        $existingExternalIds = $messagesRelation
            ->whereNotNull('external_id')
            ->pluck('external_id')
            ->map(static fn($id): string => (string)$id)
            ->flip();
        $existingDedupHashes = $messagesRelation
            ->whereNotNull('dedup_hash')
            ->pluck('dedup_hash')
            ->map(static fn($hash): string => (string)$hash)
            ->flip();

        $preparedMessages  = [];
        $copiedMediaPaths  = [];
        $messageModelClass = $parser->getMessageModelClass();
        $messages          = $importedConversation->getMessages();

        $allowedAttachmentIndexes = [];
        $attachmentSizeByIndex    = [];
        if ($canUploadMedia && $user !== null) {
            $remainingStorageBytes = max(0, $user->tariff()->getMaxStorageBytes() - $usedStorageBytes);
            $remainingMediaFiles   = max(0, $user->tariff()->getMaxMediaFilesCount() - $usedMediaFiles);

            $attachmentCandidates = [];
            foreach ($messages as $index => $message) {
                $attachmentSizeBytes = $this->messagePreparationService->estimateAttachmentSizeBytes(
                    $mediaRootPath,
                    $message
                );
                if ($attachmentSizeBytes === null || $attachmentSizeBytes < 0) {
                    continue;
                }

                $attachmentCandidates[] = [
                    'index'      => (int)$index,
                    'size_bytes' => (int)$attachmentSizeBytes,
                ];
            }

            usort($attachmentCandidates, static fn(array $a, array $b): int => $a['size_bytes'] <=> $b['size_bytes']);

            foreach ($attachmentCandidates as $candidate) {
                if ($remainingMediaFiles <= 0 || $remainingStorageBytes <= 0) {
                    break;
                }

                $sizeBytes = (int)$candidate['size_bytes'];
                if ($sizeBytes > $remainingStorageBytes) {
                    continue;
                }

                $index = (int)$candidate['index'];
                $allowedAttachmentIndexes[$index] = true;
                $attachmentSizeByIndex[$index] = $sizeBytes;
                $remainingStorageBytes -= $sizeBytes;
                $remainingMediaFiles--;
            }
        }

        foreach ($messages as $messageIndex => $message) {
            $externalId = $this->messagePreparationService->normalizeExternalId($message['external_id'] ?? null);
            $dedupHash  = $this->messagePreparationService->buildDeduplicationHash($message);

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
            $attachmentSizeBytes  = null;
            if ($canUploadMedia && isset($allowedAttachmentIndexes[$messageIndex])) {
                $attachmentStoredPath = $this->messagePreparationService->copyAttachmentForMessage(
                    $mediaRootPath,
                    $message,
                    $conversation->id
                );
                $attachmentSizeBytes = $attachmentSizeByIndex[$messageIndex] ?? null;
            } elseif (!$tariffAllowsMedia) {
                $message['attachment_export_path'] = null;
            }
            if ($attachmentStoredPath === null) {
                $attachmentSizeBytes = null;
            }
            if ($attachmentStoredPath !== null) {
                $copiedMediaPaths[$attachmentStoredPath] = true;
                $usedStorageBytes += max(0, (int)($attachmentSizeBytes ?? 0));
                $usedMediaFiles++;
            }

            $message['dedup_hash'] = $dedupHash;

            $preparedMessages[] = $this->messagePreparationService->prepareMessageRowForInsert(
                $message,
                $conversation->id,
                $messageModelClass,
                $attachmentStoredPath,
                $attachmentSizeBytes,
            );
        }

        $importedCount = 0;
        try {
            DB::transaction(function () use (
                $messageModelClass,
                $preparedMessages,
                $conversation,
                &$importedCount,
                &$copiedMediaPaths
            ) {
                /**
                 * @var PreparedMessageRowResult $prepared
                 */
                foreach ($preparedMessages as $prepared) {
                    $msg = $this->messageInsertService->createMessageSafely($messageModelClass, $prepared->getRow());
                    if ($msg === null) {
                        $preparedMedia = $prepared->getMedia();
                        $preparedPath  = is_array($preparedMedia)
                            ? ($preparedMedia['stored_path'] ?? null)
                            : null;
                        if (is_string($preparedPath) && $preparedPath !== '' && $this->mediaStorage->exists($preparedPath)) {
                            $this->mediaStorage->delete($preparedPath);
                            unset($copiedMediaPaths[$preparedPath]);
                        }

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
        } catch (QueryException $e) {
            foreach (array_keys($copiedMediaPaths) as $path) {
                if ($this->mediaStorage->exists($path)) {
                    $this->mediaStorage->delete($path);
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
}
