<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Support\FilenameSanitizer;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaFileStorageService
{
    /**
     * @param string $mediaRootPath
     * @param string $attachmentExportPath
     * @param int    $conversationId
     *
     * @return string|null
     */
    public function copyForConversation(
        string $mediaRootPath,
        string $attachmentExportPath,
        int    $conversationId,
    ): ?string {
        $resolved = $this->resolveSource($mediaRootPath, $attachmentExportPath);
        if ($resolved === null) {
            return null;
        }

        $storedRelative = sprintf('conversations/%d/media/%s', $conversationId, $resolved['basename']);
        $content        = file_get_contents($resolved['source']);
        if ($content === false) {
            return null;
        }
        Storage::put($storedRelative, $content);

        return $storedRelative;
    }

    /**
     * @param string $mediaRootPath
     * @param string $attachmentExportPath
     * @param int    $conversationId
     * @param int    $messageId
     *
     * @return string|null
     */
    public function copyForMessage(
        string $mediaRootPath,
        string $attachmentExportPath,
        int    $conversationId,
        int    $messageId
    ): ?string {
        $resolved = $this->resolveSource($mediaRootPath, $attachmentExportPath);
        if ($resolved === null) {
            return null;
        }

        $storedRelative = sprintf('conversations/%d/media/%d/%s', $conversationId, $messageId, $resolved['basename']);
        $content        = file_get_contents($resolved['source']);
        if ($content === false) {
            return null;
        }
        Storage::put($storedRelative, $content);

        return $storedRelative;
    }

    /**
     * @param string $mediaRootPath
     * @param string $attachmentExportPath
     *
     * @return array{
     *     source: string,
     *     basename: string
     * }|null
     */
    private function resolveSource(string $mediaRootPath, string $attachmentExportPath): ?array
    {
        $root = rtrim($mediaRootPath, DIRECTORY_SEPARATOR);

        foreach ($this->extractCandidateBasenames($attachmentExportPath) as $candidate) {
            $sanitizedCandidate = FilenameSanitizer::sanitize($candidate);
            if ($sanitizedCandidate === 'file') {
                continue;
            }
            $found = $this->findFileByBasename($root, $sanitizedCandidate);
            if ($found !== null) {
                return [
                    'source'   => $found,
                    'basename' => $sanitizedCandidate,
                ];
            }
        }

        Log::debug('Import media file not found', [
            'export_path' => $attachmentExportPath,
            'basename'    => basename(str_replace('\\', '/', $attachmentExportPath)),
            'root'        => $mediaRootPath,
        ]);

        return null;
    }

    /**
     * Поддержка legacy export_path без слешей.
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
     * @param string $dir
     * @param string $basename
     *
     * @return string|null
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
