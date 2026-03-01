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

            // 1) Сообщения с указанным путём — ищем файл по имени/пути
            $withPath = $relation
                ->whereNotNull('attachment_export_path')
                ->whereNull('attachment_stored_path')
                ->get(['id', 'attachment_export_path']);
            foreach ($withPath as $message) {
                $storedPath = $this->copyMediaIfFound(
                    $absoluteExtracted,
                    $message->attachment_export_path,
                    $conversation->id
                );
                if ($storedPath !== null) {
                    $model->newQuery()->where('id', $message->id)->update(['attachment_stored_path' => $storedPath]);
                }
            }

            // 2) Медиа без пути (например WhatsApp) — сопоставление по порядку
            $mediaTypes = ['photo', 'voice_message', 'video_message', 'media', 'sticker', 'document', 'gif', 'video'];
            $withoutPath = $relation
                ->whereNull('attachment_stored_path')
                ->where(function ($q) use ($mediaTypes) {
                    $q->whereNull('attachment_export_path')->orWhere('attachment_export_path', '');
                })
                ->whereIn('message_type', $mediaTypes)
                ->orderBy('sent_at')
                ->get(['id']);
            $files = $this->collectAllFiles($absoluteExtracted);
            foreach ($withoutPath as $idx => $message) {
                if (!isset($files[$idx])) {
                    break;
                }
                $storedPath = $this->copyFileToStorage($files[$idx], $conversation->id, $idx);
                if ($storedPath !== null) {
                    $model->newQuery()->where('id', $message->id)->update(['attachment_stored_path' => $storedPath]);
                }
            }
        } finally {
            Storage::deleteDirectory($extractedDir);
            if (Storage::exists($this->path)) {
                Storage::delete($this->path);
            }
        }
    }

    private function copyMediaIfFound(string $mediaRoot, string $attachmentExportPath, int $conversationId): ?string
    {
        $exportPath = str_replace(['\\', '../'], ['/', ''], $attachmentExportPath);
        $exportPath = ltrim($exportPath, '/');
        if ($exportPath === '') {
            return null;
        }
        $sourceAbsolute = rtrim($mediaRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $exportPath);
        if (!is_file($sourceAbsolute)) {
            return null;
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

    /** @return list<string> Абсолютные пути к файлам (без каталогов), отсортированы. */
    private function collectAllFiles(string $dir): array
    {
        $list = [];
        $this->collectFilesRecursive($dir, $list);
        sort($list);

        return $list;
    }

    private function collectFilesRecursive(string $dir, array &$list): void
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
                $list[] = $full;
            } else {
                $this->collectFilesRecursive($full, $list);
            }
        }
    }

    private function copyFileToStorage(string $absolutePath, int $conversationId, int $index): ?string
    {
        if (!is_file($absolutePath)) {
            return null;
        }
        $ext            = pathinfo($absolutePath, PATHINFO_EXTENSION);
        $ext            = FilenameSanitizer::sanitize($ext);
        $name           = ($ext !== '' && $ext !== 'file') ? "media_{$index}.{$ext}" : "media_{$index}";
        $storedRelative = sprintf('conversations/%d/media/%s', $conversationId, $name);
        $content        = file_get_contents($absolutePath);
        if ($content === false) {
            return null;
        }
        Storage::put($storedRelative, $content);

        return $storedRelative;
    }
}
