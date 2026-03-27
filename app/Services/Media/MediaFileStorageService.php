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
        if (!$this->storeStream($resolved['source'], $storedRelative)) {
            return null;
        }

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
        if (!$this->storeStream($resolved['source'], $storedRelative)) {
            return null;
        }

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

        $normalizedExportPath = $this->normalizeExportPath($attachmentExportPath);
        if ($normalizedExportPath !== null) {
            $exactPath = $this->findFileByRelativePath($root, $normalizedExportPath);
            if ($exactPath !== null) {
                return [
                    'source'   => $exactPath,
                    'basename' => FilenameSanitizer::sanitize(basename($normalizedExportPath)),
                ];
            }
        }

        foreach ($this->extractCandidateBasenames($attachmentExportPath) as $candidate) {
            $sanitizedCandidate = FilenameSanitizer::sanitize($candidate);
            if ($sanitizedCandidate === 'file') {
                continue;
            }
            $found = $this->findUniqueFileByBasename($root, $sanitizedCandidate);
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

    private function storeStream(string $sourcePath, string $storedRelative): bool
    {
        $stream = @fopen($sourcePath, 'rb');
        if (!is_resource($stream)) {
            return false;
        }

        try {
            return Storage::put($storedRelative, $stream) === true;
        } finally {
            fclose($stream);
        }
    }

    private function normalizeExportPath(string $attachmentExportPath): ?string
    {
        $normalized = trim(str_replace('\\', '/', $attachmentExportPath));
        if ($normalized === '') {
            return null;
        }

        return ltrim($normalized, '/');
    }

    private function findFileByRelativePath(string $root, string $relativePath): ?string
    {
        if (!$this->isSafeRelativePath($relativePath)) {
            return null;
        }

        $candidate = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (!is_file($candidate)) {
            return null;
        }

        $candidateReal = realpath($candidate);
        $rootReal      = realpath($root);
        if ($candidateReal === false || $rootReal === false) {
            return null;
        }

        return $this->isPathInsideRoot($candidateReal, $rootReal)
            ? $candidateReal
            : null;
    }

    private function isSafeRelativePath(string $relativePath): bool
    {
        if ($relativePath === '' || str_contains($relativePath, "\0")) {
            return false;
        }

        if (str_starts_with($relativePath, '/')) {
            return false;
        }

        $parts = explode('/', str_replace('\\', '/', $relativePath));

        return !in_array('..', $parts, true);
    }

    private function isPathInsideRoot(string $candidatePath, string $rootPath): bool
    {
        if ($candidatePath === $rootPath) {
            return true;
        }

        $prefix = rtrim($rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($candidatePath . DIRECTORY_SEPARATOR, $prefix);
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
    private function findUniqueFileByBasename(string $dir, string $basename): ?string
    {
        if (!is_dir($dir)) {
            return null;
        }

        $target = strtolower(FilenameSanitizer::sanitize($basename));
        if ($target === '' || $target === 'file') {
            return null;
        }

        $matches = [];
        $this->collectMatchesByBasename($dir, $target, $matches, 2);
        if (count($matches) === 1) {
            return $matches[0];
        }
        if (count($matches) > 1) {
            Log::warning('Import media file match is ambiguous', [
                'basename' => $basename,
                'root'     => $dir,
                'count'    => count($matches),
            ]);
        }

        return null;
    }

    /**
     * @param array<int, string> $matches
     */
    private function collectMatchesByBasename(string $dir, string $target, array &$matches, int $limit): void
    {
        if (count($matches) >= $limit) {
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
            $full = $dir . $sep . $item;
            if (is_file($full)) {
                $sanitized = strtolower(FilenameSanitizer::sanitize($item));
                if ($sanitized === $target) {
                    $matches[] = $full;
                    if (count($matches) >= $limit) {
                        return;
                    }
                }
            }
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $dir . $sep . $item;
            if (is_dir($full)) {
                $this->collectMatchesByBasename($full, $target, $matches, $limit);
            }
        }
    }
}
