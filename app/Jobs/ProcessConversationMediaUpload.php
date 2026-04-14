<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\MediaAttachment;
use App\Models\MediaTypes\SupportedMediaTypesEnum;
use App\Models\User;
use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;
use App\Services\Import\Factories\ImportArchiveExtractorFactory;
use App\Services\Media\ImportedMediaResolverService;
use App\Services\Media\Storage\MediaStorageInterface;
use App\Services\Parsers\ParserRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessConversationMediaUpload implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param int    $userId
     * @param int    $conversationId
     * @param string $path
     */
    public function __construct(
        public int    $userId,
        public int    $conversationId,
        public string $path
    ) {
    }

    /**
     * @param ParserRegistry                $parserRegistry
     * @param ImportedMediaResolverService  $importedMediaResolverService
     * @param MediaStorageInterface         $mediaStorage
     * @param ImportArchiveExtractorFactory $archiveExtractorsFactory
     *
     * @return void
     */
    public function handle(
        ParserRegistry                $parserRegistry,
        ImportedMediaResolverService  $importedMediaResolverService,
        MediaStorageInterface         $mediaStorage,
        ImportArchiveExtractorFactory $archiveExtractorsFactory
    ): void {
        $importsTmpDiskName = (string)config('filesystems.imports_tmp_disk', 'imports_tmp');
        $importsTmpDisk     = Storage::disk($importsTmpDiskName);
        $extractedDir       = null;

        $conversation = Conversation::with('messengerAccount')->find($this->conversationId);
        if (!$conversation || $conversation->messengerAccount->user_id !== $this->userId) {
            return;
        }

        $user = User::find($this->userId);
        if (!$user || !$user->canUploadMedia()) {
            return;
        }

        try {
            $archiveExtractor = $archiveExtractorsFactory->makeForPath($this->path);
            if ($archiveExtractor === null) {
                return;
            }

            $source       = $archiveExtractor->extract($this->path, $conversation->messengerAccount->type);
            $extractedDir = $source->getExtractedDir();
            if ($extractedDir === null) {
                return;
            }

            // Для догрузки медиа нужен весь распакованный архив, а не messenger-specific media root.
            $absoluteExtracted = $importsTmpDisk->path($extractedDir);

            $parser   = $parserRegistry->get($conversation->messengerAccount->type);
            $relation = $parser->getMessagesRelation($conversation);
            $model    = $relation->getRelated();

            $pending = MediaAttachment::query()
                ->where('conversation_id', $conversation->id)
                ->whereNotNull('export_path')
                ->where(function ($q): void {
                    $q->whereNull('stored_path')->orWhere('stored_path', '');
                })
                ->orderBy('id')
                ->get();

            $messageIdByAttachmentId = $model->newQuery()
                ->whereIn('media_attachment_id', $pending->pluck('id')->all())
                ->pluck('id', 'media_attachment_id');

            foreach ($pending as $media) {
                $messageId = $messageIdByAttachmentId->get($media->id);
                if ($messageId === null) {
                    continue;
                }

                $storedPath = $importedMediaResolverService->copyForMessage(
                    $absoluteExtracted,
                    (string)$media->export_path,
                    $conversation->id,
                    (int)$messageId
                );
                if ($storedPath === null) {
                    continue;
                }

                $mime = $mediaStorage->mimeType($storedPath);
                $media->update([
                    'stored_path'       => $storedPath,
                    'media_type'        => SupportedMediaTypesEnum::detect($mime
                        ?: null, $media->export_path)?->value,
                    'mime_type'         => $mime
                        ?: null,
                    'original_filename' => basename($storedPath),
                ]);
            }
        } catch (ArchiveExtractionFailedException $e) {
            Log::warning('Archive extraction failed', [
                'user_id'         => $this->userId,
                'conversation_id' => $this->conversationId,
                'path'            => $this->path,
                'reason'          => $e->getMessage(),
            ]);
        } finally {
            if (isset($extractedDir) && $importsTmpDisk->exists($extractedDir)) {
                $importsTmpDisk->deleteDirectory($extractedDir);
            }
            if ($importsTmpDisk->exists($this->path)) {
                $importsTmpDisk->delete($this->path);
            }
        }
    }
}
