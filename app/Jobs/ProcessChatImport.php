<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;
use App\Services\Import\Factories\ImportArchiveExtractorFactory;
use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
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
     * @param string                  $exportFileStoredPath Storage-relative path (ZIP or single export file)
     * @param ImportStrategyInterface $strategy
     */
    public function __construct(
        public int                     $userId,
        public string                  $messengerType,
        public string                  $exportFileStoredPath,
        public ImportStrategyInterface $strategy
    ) {
    }

    /**
     * @param ImportArchiveExtractorFactory $archiveExtractorsFactory
     *
     * @return void
     */
    public function handle(ImportArchiveExtractorFactory $archiveExtractorsFactory): void
    {
        $source       = null;
        $extractedDir = null;

        try {
            $archiveExtractor = $archiveExtractorsFactory->makeForPath($this->exportFileStoredPath);
            if ($archiveExtractor !== null) {
                $source = $archiveExtractor->extract($this->exportFileStoredPath, $this->messengerType);

                if (!isset($source)) {
                    return;
                }

                $extractedDir = $source->getExtractedDir();

                if ($source->getExportFileAbsolutePath() === null) {
                    return;
                }
            }
            $extractedExportFile = $source ?? new ArchiveExtractionResult(
                Storage::path($this->exportFileStoredPath),
                null,
                null
            );

            /**
             * @var ImportService $service
             */
            $service = app(ImportService::class);

            /**
             * Импорт читает файл экспорта из $extractedExportFile и при наличии медиа копирует их из распакованной
             * папки в постоянное хранилище (Storage).
             * К моменту выхода из import() файлы уже лежат в conversations/{id}/media/.
             */
            $service->import(
                userId: $this->userId,
                messengerType: $this->messengerType,
                strategy: $this->strategy,
                extractedExportFile: $extractedExportFile
            );
        } catch (ArchiveExtractionFailedException $e) {
            Log::warning('Archive extraction failed', [
                'user_id'          => $this->userId,
                'messenger_type'   => $this->messengerType,
                'export_file_path' => $this->exportFileStoredPath,
                'reason'           => $e->getMessage(),
            ]);
        } finally {
            /**
             * Delete temporary archive/export file
             */
            if (Storage::exists($this->exportFileStoredPath)) {
                Storage::delete($this->exportFileStoredPath);
            }
            if (isset($extractedDir) && Storage::exists($extractedDir)) {
                Storage::deleteDirectory($extractedDir);
            }
        }
    }
}
