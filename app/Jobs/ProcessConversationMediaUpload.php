<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Conversation;
use App\Models\MediaAttachment;
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

                $storedPath = $this->copyMediaIfFound(
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
                    'mime_type'         => $mime ?: null,
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

    private function copyMediaIfFound(
        string $mediaRoot,
        string $attachmentExportPath,
        int    $conversationId,
        int    $messageId
    ): ?string {
        $sourceAbsolute = null;
        $basename       = null;
        foreach ($this->extractCandidateBasenames($attachmentExportPath) as $candidate) {
            $sanitizedCandidate = FilenameSanitizer::sanitize($candidate);
            if ($sanitizedCandidate === 'file') {
                continue;
            }
            $found = $this->findFileByBasename($mediaRoot, $sanitizedCandidate);
            if ($found !== null) {
                $sourceAbsolute = $found;
                $basename       = $sanitizedCandidate;
                break;
            }
        }
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
     * Для обратной совместимости поддерживаем "сплющенные" export_path без слешей.
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
