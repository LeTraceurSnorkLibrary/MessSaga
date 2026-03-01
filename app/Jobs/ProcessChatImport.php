<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class ProcessChatImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int                     $userId
     * @param string                  $messengerType
     * @param string                  $path Storage-relative path (ZIP or single export file)
     * @param ImportStrategyInterface $strategy
     */
    public function __construct(
        public int                     $userId,
        public string                  $messengerType,
        public string                  $path,
        public ImportStrategyInterface $strategy
    ) {
    }

    /**
     * @throws Throwable
     * @return void
     */
    public function handle(): void
    {
        $pathToUse     = $this->path;
        $mediaRootPath = null;
        $extractedDir  = null;

        try {
            if ($this->isZipPath($this->path)) {
                [$pathToUse, $mediaRootPath, $extractedDir] = $this->unpackAndLocateExport();
                if ($pathToUse === null) {
                    return;
                }
            }

            /**
             * @var ImportService $service
             */
            $service = app(ImportService::class);

            // Импорт читает экспорт из pathToUse и при наличии mediaRootPath копирует
            // все медиа из распакованной папки в постоянное хранилище (Storage).
            // К моменту выхода из import() файлы уже лежат в conversations/{id}/media/.
            $service->import(
                userId: $this->userId,
                messengerType: $this->messengerType,
                path: $pathToUse,
                strategy: $this->strategy,
                mediaRootPath: $mediaRootPath
            );
        } finally {
            // Удаляем только после того, как import() уже скопировал файлы в Storage.
            // Временная папка и архив больше не нужны.
            if (Storage::exists($this->path)) {
                Storage::delete($this->path);
            }
            if ($extractedDir !== null && Storage::exists($extractedDir)) {
                $this->deleteDirectory($extractedDir);
            }
        }
    }

    private function isZipPath(string $path): bool
    {
        return str_ends_with(strtolower($path), '.zip');
    }

    /**
     * Распаковывает ZIP в каталог в Storage, находит файл экспорта по правилам мессенджера.
     *
     * @return array{string|null, string|null, string|null} [storagePathToExport, absoluteMediaRoot,
     *                            extractedStoragePath]
     */
    private function unpackAndLocateExport(): array
    {
        $absoluteZip = Storage::path($this->path);
        if (!is_file($absoluteZip)) {
            return [null, null, null];
        }

        $extractedDir      = 'chat_imports/extracted_' . uniqid('', true);
        $absoluteExtracted = Storage::path($extractedDir);

        $zip = new ZipArchive();
        if ($zip->open($absoluteZip, ZipArchive::RDONLY) !== true) {
            return [null, null, null];
        }

        $zip->extractTo($absoluteExtracted);
        $zip->close();

        // Ищем файл экспорта по правилам для типа мессенджера (рекурсивно по архиву)
        $exportRelativePath = $this->findExportFileByMessenger($absoluteExtracted, $this->messengerType);
        if ($exportRelativePath === null) {
            Log::warning('ProcessChatImport: в архиве не найден файл экспорта', [
                'path'           => $this->path,
                'messenger_type' => $this->messengerType,
                'extracted'      => $extractedDir,
            ]);

            return [null, null, $extractedDir];
        }

        $relativeExport = $extractedDir . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $exportRelativePath);
        // Корень медиа — каталог, где лежит файл экспорта (пути из экспорта относительно него)
        $exportDir     = dirname($exportRelativePath);
        $mediaRootPath = $exportDir === '.'
            ? $absoluteExtracted
            : $absoluteExtracted . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $exportDir);

        return [$relativeExport, $mediaRootPath, $extractedDir];
    }

    /**
     * Ищет файл экспорта в распакованном архиве по правилам для типа мессенджера.
     * Возвращает путь относительно $absoluteExtractedRoot.
     *
     * Telegram: result.json, иначе любой .json
     * WhatsApp: .txt с "WhatsApp" в имени, иначе любой .txt
     */
    private function findExportFileByMessenger(string $absoluteExtractedRoot, string $messengerType): ?string
    {
        $messengerType = strtolower($messengerType);

        if ($messengerType === 'telegram') {
            return $this->findTelegramExportFile($absoluteExtractedRoot, '');
        }
        if ($messengerType === 'whatsapp') {
            return $this->findWhatsAppExportFile($absoluteExtractedRoot, '');
        }

        // viber / неизвестный: сначала .json (result.json), потом .txt с "whatsapp" или любой .txt
        return $this->findTelegramExportFile($absoluteExtractedRoot, '')
            ?? $this->findWhatsAppExportFile($absoluteExtractedRoot, '');
    }

    /**
     * Telegram: сначала result.json (рекурсивно), если не найден — любой .json.
     */
    private function findTelegramExportFile(string $absoluteDir, string $relativePrefix): ?string
    {
        $found = $this->findFileRecursive($absoluteDir, $relativePrefix, 'result.json', fn (string $name
        ) => strtolower($name) === 'result.json');
        if ($found !== null) {
            return $found;
        }

        return $this->findFileRecursive($absoluteDir, $relativePrefix, null, fn (string $name
        ) => str_ends_with(strtolower($name), '.json'));
    }

    /**
     * Рекурсивный поиск одного файла: либо с именем $exactName, либо первый, для которого $predicate(name) true.
     */
    private function findFileRecursive(string    $absoluteDir,
                                       string    $relativePrefix,
                                       ?string   $exactName,
                                       ?callable $predicate
    ): ?string {
        if (!is_dir($absoluteDir)) {
            return null;
        }
        $sep   = DIRECTORY_SEPARATOR;
        $items = @scandir($absoluteDir);
        if ($items === false) {
            return null;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $absoluteDir . $sep . $item;
            if (is_file($full)) {
                if ($exactName !== null && strcasecmp($item, $exactName) === 0) {
                    return $relativePrefix
                        ? $relativePrefix . '/' . $item
                        : $item;
                }
                if ($predicate !== null && $predicate($item)) {
                    return $relativePrefix
                        ? $relativePrefix . '/' . $item
                        : $item;
                }
            }
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $full = $absoluteDir . $sep . $item;
            if (is_dir($full)) {
                $prefix = $relativePrefix
                    ? $relativePrefix . '/' . $item
                    : $item;
                $found  = $this->findFileRecursive($full, $prefix, $exactName, $predicate);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * WhatsApp: сначала .txt с "whatsapp" в имени (рекурсивно), иначе любой .txt.
     */
    private function findWhatsAppExportFile(string $absoluteDir, string $relativePrefix): ?string
    {
        $found = $this->findFileRecursive($absoluteDir, $relativePrefix, null, function (string $name): bool {
            $lower = strtolower($name);

            return str_ends_with($lower, '.txt') && str_contains($lower, 'whatsapp');
        });
        if ($found !== null) {
            return $found;
        }

        return $this->findFileRecursive($absoluteDir, $relativePrefix, null, fn (string $name
        ) => str_ends_with(strtolower($name), '.txt'));
    }

    private function deleteDirectory(string $storagePath): void
    {
        Storage::deleteDirectory($storagePath);
    }
}
