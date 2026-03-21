<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Import\ArchiveImportPreparationService;
use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessChatImport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
     * @param ArchiveImportPreparationService $archivePreparation
     *
     * @return void
     */
    public function handle(ArchiveImportPreparationService $archivePreparation): void
    {
        $pathToUse     = $this->path;
        $mediaRootPath = null;
        $extractedDir  = null;

        try {
            if ($archivePreparation->isZipPath($this->path)) {
                $prepared      = $archivePreparation->unpackAndLocateExport($this->path, $this->messengerType);
                $pathToUse     = $prepared['path_to_use'];
                $mediaRootPath = $prepared['media_root_path'];
                $extractedDir  = $prepared['extracted_dir'];

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
                Storage::deleteDirectory($extractedDir);
            }
        }
    }
}
