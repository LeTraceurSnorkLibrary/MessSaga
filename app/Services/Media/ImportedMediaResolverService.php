<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Services\Media\Storage\MediaStorageInterface;
use App\Support\FilenameSanitizer;
use Illuminate\Support\Facades\Log;

class ImportedMediaResolverService
{
    /**
     * Кэш индексов файлов по корню распакованного экспорта:
     * [rootPath => [sanitizedLowerBasename => [absolutePath1, absolutePath2...]]]
     *
     * @var array<string, array<string, array<int, string>>>
     */
    private array $basenameIndexCache = [];

    /**
     * @param MediaStorageInterface $mediaStorage
     */
    public function __construct(
        private readonly MediaStorageInterface $mediaStorage
    ) {
    }

    /**
     * Копирует медиа-файл из временно распакованного экспорта
     * в постоянное хранилище переписки (без привязки к конкретному сообщению).
     *
     * @see self::resolveSource() Логика безопасного резолва исходного файла.
     *
     * @param string $attachmentExportPath Путь к вложению из export-данных сообщения.
     * @param int    $conversationId       Идентификатор переписки.
     *
     * @param string $mediaRootPath        Абсолютный путь до корня распакованного экспорта.
     *
     * @return string|null Относительный путь в Storage при успехе, иначе null.
     */
    public function copyForConversation(
        string $mediaRootPath,
        string $attachmentExportPath,
        int $conversationId,
    ): ?string {
        $resolved = $this->resolveImportSource($mediaRootPath, $attachmentExportPath);
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
     * Копирует медиа-файл из временно распакованного экспорта
     * в постоянное хранилище переписки с привязкой к конкретному сообщению.
     *
     * @see self::resolveSource() Логика безопасного резолва исходного файла.
     *
     * @param string $attachmentExportPath Путь к вложению из export-данных сообщения.
     * @param int    $conversationId       Идентификатор переписки.
     * @param int    $messageId            Идентификатор сообщения.
     *
     * @param string $mediaRootPath        Абсолютный путь до корня распакованного экспорта.
     *
     * @return string|null Относительный путь в Storage при успехе, иначе null.
     */
    public function copyForMessage(
        string $mediaRootPath,
        string $attachmentExportPath,
        int $conversationId,
        int $messageId
    ): ?string {
        $resolved = $this->resolveImportSource($mediaRootPath, $attachmentExportPath);
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
     * @return int|null
     */
    public function estimateAttachmentSizeBytes(
        string $mediaRootPath,
        string $attachmentExportPath
    ): ?int {
        $resolved = $this->resolveImportSource($mediaRootPath, $attachmentExportPath);

        return $resolved['size_bytes'] ?? null;
    }

    /**
     * @param string $mediaRootPath
     * @param string $attachmentExportPath
     *
     * @return array{
     *     source: string,
     *     basename: string,
     *     size_bytes: int
     * }|null
     */
    public function resolveImportSource(string $mediaRootPath, string $attachmentExportPath): ?array
    {
        return $this->resolveSource($mediaRootPath, $attachmentExportPath);
    }

    /**
     * Разрешает исходный файл вложения в распакованном экспорте.
     * Сначала пытается найти файл по точному export_path,
     * затем использует fallback по basename для legacy-экспортов.
     *
     * @param string $mediaRootPath        Абсолютный путь до корня распакованного экспорта.
     * @param string $attachmentExportPath Путь к вложению из export-данных сообщения.
     *
     * @return array{
     *     source: string,
     *     basename: string,
     *     size_bytes: int
     * }|null
     */
    private function resolveSource(string $mediaRootPath, string $attachmentExportPath): ?array
    {
        $root = rtrim($mediaRootPath, DIRECTORY_SEPARATOR);

        $exactPath = $this->tryResolveByExportPath($root, $attachmentExportPath);
        if ($exactPath !== null) {
            return [
                'source'   => $exactPath,
                'basename' => FilenameSanitizer::sanitize(basename(str_replace('\\', '/', $attachmentExportPath))),
                'size_bytes' => max(0, (int)(@filesize($exactPath) ?: 0)),
            ];
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
                    'size_bytes' => max(0, (int)(@filesize($found) ?: 0)),
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
     * Пытается разрешить вложение по точному пути из export-данных.
     * Выполняет базовые проверки безопасности пути и гарантирует,
     * что итоговый файл расположен внутри корня распакованного экспорта.
     *
     * @param string $root                 Абсолютный путь до корня распакованного экспорта.
     * @param string $attachmentExportPath Путь к вложению из export-данных сообщения.
     *
     * @return string|null Абсолютный путь к найденному файлу или null.
     */
    private function tryResolveByExportPath(string $root, string $attachmentExportPath): ?string
    {
        $relativePath = trim(str_replace('\\', '/', $attachmentExportPath));
        if ($relativePath === '' || str_contains($relativePath, "\0")) {
            return null;
        }

        $relativePath = ltrim($relativePath, '/');
        $parts        = explode('/', $relativePath);
        if (in_array('..', $parts, true)) {
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

        if ($candidateReal === $rootReal) {
            return $candidateReal;
        }

        $prefix = rtrim($rootReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return str_starts_with($candidateReal . DIRECTORY_SEPARATOR, $prefix)
            ? $candidateReal
            : null;
    }

    /**
     * Потоково записывает исходный файл в Storage по целевому относительному пути.
     * Используется вместо загрузки всего файла в память.
     *
     * @param string $sourcePath     Абсолютный путь к исходному файлу на диске.
     * @param string $storedRelative Относительный путь назначения в Storage.
     *
     * @return bool true, если файл успешно записан.
     */
    private function storeStream(string $sourcePath, string $storedRelative): bool
    {
        $stream = @fopen($sourcePath, 'rb');
        if (!is_resource($stream)) {
            return false;
        }

        try {
            return $this->mediaStorage->putStream($storedRelative, $stream);
        } finally {
            fclose($stream);
        }
    }

    /**
     * Формирует список кандидатов basename для legacy export_path без слешей.
     * Например, из "001_ABC_photo.jpg" дополнительно извлекается "ABC_photo.jpg" и "photo.jpg".
     *
     * @param string $attachmentExportPath Путь к вложению из export-данных сообщения.
     *
     * @return array<int, string> Уникальный список кандидатов basename в порядке приоритета.
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
     * Ищет файл по basename рекурсивно в директории и возвращает путь,
     * только если найдено ровно одно совпадение.
     * При нескольких совпадениях возвращает null и пишет warning в лог.
     *
     * @param string $dir      Абсолютный путь до корневой директории поиска.
     * @param string $basename Искомое имя файла (или candidate из legacy-правила).
     *
     * @return string|null Абсолютный путь к единственному найденному файлу или null.
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

        $index   = $this->getBasenameIndex($dir);
        $matches = $index[$target] ?? [];

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
     * Строит (и кэширует) индекс файлов по basename.
     *
     * @param string $root
     *
     * @return array<string, array<int, string>>
     */
    private function getBasenameIndex(string $root): array
    {
        if (isset($this->basenameIndexCache[$root])) {
            return $this->basenameIndexCache[$root];
        }

        if (!is_dir($root)) {
            $this->basenameIndexCache[$root] = [];

            return $this->basenameIndexCache[$root];
        }

        $index = [];
        $stack = [$root];

        while ($stack !== []) {
            $scanDir = array_pop($stack);
            if (!is_string($scanDir)) {
                continue;
            }

            $items = @scandir($scanDir);
            if ($items === false) {
                continue;
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $fullPath = $scanDir . DIRECTORY_SEPARATOR . $item;
                if (is_dir($fullPath)) {
                    $stack[] = $fullPath;
                    continue;
                }

                if (!is_file($fullPath)) {
                    continue;
                }

                $sanitized = strtolower(FilenameSanitizer::sanitize($item));
                if ($sanitized === '' || $sanitized === 'file') {
                    continue;
                }

                $index[$sanitized][] = $fullPath;
            }
        }

        $this->basenameIndexCache[$root] = $index;

        return $this->basenameIndexCache[$root];
    }
}
