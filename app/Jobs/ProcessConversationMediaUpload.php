<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Conversation;
use App\Services\Parsers\ParserRegistry;
use App\Support\FilenameSanitizer;
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

    public function __construct(
        public int    $userId,
        public int    $conversationId,
        public string $path
    ) {
    }

    public function handle(ParserRegistry $parserRegistry): void
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

            // Сообщения с указанным путём — ищем файл по имени/пути
            $withPath = $relation
                ->whereNotNull('attachment_export_path')
                ->whereNull('attachment_stored_path')
                ->orderBy('sent_at')
                ->get(['id', 'attachment_export_path']);
            foreach ($withPath as $message) {
                $storedPath = $this->copyMediaIfFound(
                    $absoluteExtracted,
                    $message->attachment_export_path,
                    $conversation->id,
                    $message->id
                );
                if ($storedPath !== null) {
                    $model->newQuery()
                        ->where('id', $message->id)
                        ->update(['attachment_stored_path' => $storedPath]);
                }
            }
        } finally {
            Storage::deleteDirectory($extractedDir);
            if (Storage::exists($this->path)) {
                Storage::delete($this->path);
            }
        }
    }

    private function copyMediaIfFound(
        string $mediaRoot,
        string $attachmentExportPath,
        int    $conversationId,
        int    $messageId
    ): ?string {
        // Используем только очищенное имя файла для поиска и сохранения.
        $basename       = FilenameSanitizer::sanitize(basename($attachmentExportPath));
        $sourceAbsolute = $this->findFileByBasename($mediaRoot, $basename);
        if ($sourceAbsolute === null) {
            return null;
        }

        // conversations/<ID_переписки>/media/<ID_сообщения>/<Название_файла>
        $storedRelative = sprintf('conversations/%d/media/%d/%s', $conversationId, $messageId, $basename);
        $content        = file_get_contents($sourceAbsolute);
        if ($content === false) {
            return null;
        }
        Storage::put($storedRelative, $content);

        return $storedRelative;
    }

    /**
     * Рекурсивный поиск файла по имени в каталоге (та же логика, что и в ImportService).
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
