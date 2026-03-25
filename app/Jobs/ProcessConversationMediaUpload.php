<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\MediaAttachment;
use App\Models\MediaTypes\SupportedMediaTypesEnum;
use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;
use App\Services\Import\Factories\ImportArchiveExtractorFactory;
use App\Services\Media\MediaFileStorageService;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
     * @param MediaFileStorageService       $mediaFileStorageService
     * @param ImportArchiveExtractorFactory $archiveExtractorsFactory
     *
     * @return void
     */
    public function handle(
        ParserRegistry                $parserRegistry,
        MediaFileStorageService       $mediaFileStorageService,
        ImportArchiveExtractorFactory $archiveExtractorsFactory
    ): void {
        $extractedDir = null;

        $conversation = Conversation::with('messengerAccount')->find($this->conversationId);
        if (!$conversation || $conversation->messengerAccount->user_id !== $this->userId) {
            return;
        }

        try {
            $archiveExtractor = $archiveExtractorsFactory->makeForPath($this->path);
            if ($archiveExtractor === null) {
                return;
            }

            $source = $archiveExtractor->extract($this->path, $conversation->messengerAccount->type);
            if ($source === null) {
                return;
            }

            $extractedDir = $source->getExtractedDir();
            if ($extractedDir === null) {
                return;
            }

            $absoluteExtracted = $source->getMediaRootPath() ?? Storage::path($extractedDir);
            if ($absoluteExtracted === null) {
                return;
            }

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

            foreach ($pending as $media) {
                $message = $model->newQuery()
                    ->where('media_attachment_id', $media->id)
                    ->first(['id']);
                if ($message === null) {
                    continue;
                }

                $storedPath = $mediaFileStorageService->copyForMessage(
                    $absoluteExtracted,
                    (string)$media->export_path,
                    $conversation->id,
                    (int)$message->id
                );
                if ($storedPath === null) {
                    continue;
                }

                $mime = Storage::mimeType($storedPath);
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
            if (isset($extractedDir) && Storage::exists($extractedDir)) {
                Storage::deleteDirectory($extractedDir);
            }
            if (Storage::exists($this->path)) {
                Storage::delete($this->path);
            }
        }
    }
}
