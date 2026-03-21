<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\MediaAttachment;
use App\Models\MediaTypes\SupportedMediaTypesEnum;
use App\Services\Media\MediaFileStorageService;
use App\Services\Parsers\ParserRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

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
     * @param ParserRegistry          $parserRegistry
     * @param MediaFileStorageService $mediaFileStorageService
     *
     * @return void
     */
    public function handle(ParserRegistry $parserRegistry, MediaFileStorageService $mediaFileStorageService): void
    {
        $conversation = Conversation::with('messengerAccount')->find($this->conversationId);
        if (!$conversation || $conversation->messengerAccount->user_id !== $this->userId) {
            return;
        }

        $absoluteZip = Storage::path($this->path);
        if (!is_file($absoluteZip)) {
            return;
        }

        $extractedDir      = 'chat_imports/media_upload_' . uniqid('', true);
        $absoluteExtracted = Storage::path($extractedDir);

        $zip = new ZipArchive();
        if ($zip->open($absoluteZip, ZipArchive::RDONLY) !== true) {
            return;
        }
        $zip->extractTo($absoluteExtracted);
        $zip->close();

        try {
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
                    'media_type'        => SupportedMediaTypesEnum::detect($mime ?: null, $media->export_path)?->value,
                    'mime_type'         => $mime
                        ?: null,
                    'original_filename' => basename($storedPath),
                ]);
            }
        } finally {
            Storage::deleteDirectory($extractedDir);
            if (Storage::exists($this->path)) {
                Storage::delete($this->path);
            }
        }
    }

}
