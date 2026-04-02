<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;
use App\Services\Import\Factories\ImportArchiveExtractorFactory;
use App\Services\Import\Strategies\ImportOnlyMediaFilesStrategyInterface;
use App\Services\Import\Strategies\ImportStrategyInterface;
use App\Services\ImportService;
use App\Services\Media\ImportedMediaResolverService;
use App\Services\Media\Storage\MediaStorageInterface;
use App\Services\Parsers\ParserRegistry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
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
     * @param string                  $exportFileStoredPath Storage-relative path (archive or single export file)
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
     * @param ParserRegistry                $parserRegistry
     * @param ImportedMediaResolverService  $importedMediaResolverService
     * @param MediaStorageInterface         $mediaStorage
     *
     * @return void
     */
    public function handle(
        ImportArchiveExtractorFactory $archiveExtractorsFactory,
        ParserRegistry                $parserRegistry,
        ImportedMediaResolverService  $importedMediaResolverService,
        MediaStorageInterface         $mediaStorage
    ): void {
        $importsTmpDiskName = (string)config('filesystems.imports_tmp_disk', 'imports_tmp');
        $importsTmpDisk     = Storage::disk($importsTmpDiskName);
        $source             = null;
        $extractedDir       = null;
        $shouldDeleteSource = true;

        try {
            $archiveExtractor = $archiveExtractorsFactory->makeForPath($this->exportFileStoredPath);
            if ($archiveExtractor !== null) {
                $source = $archiveExtractor->extract($this->exportFileStoredPath, $this->messengerType);

                $extractedDir = $source->getExtractedDir();

                /**
                 * If export file (e.g. result.json) is not presented - we cannot perform import
                 */
                if ($source->getExportFileAbsolutePath() === null) {
                    /**
                     * Though, if import mode is 'To selected conversation' - we can try to import media to already
                     * existing conversation
                     */
                    $this->runMediaOnlyFallback(
                        archiveExtractorsFactory: $archiveExtractorsFactory,
                        parserRegistry: $parserRegistry,
                        importedMediaResolverService: $importedMediaResolverService,
                        mediaStorage: $mediaStorage
                    );

                    return;
                }
            }
            $extractedExportFile = $source ?? new ArchiveExtractionResult(
                $importsTmpDisk->path($this->exportFileStoredPath),
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
        } catch (QueryException $e) {
            Log::error('Import failed due to database query error', [
                'user_id'          => $this->userId,
                'messenger_type'   => $this->messengerType,
                'export_file_path' => $this->exportFileStoredPath,
                'strategy'         => $this->strategy->getName(),
                'error'            => $e->getMessage(),
            ]);

            $shouldDeleteSource = false;
            throw $e;
        } finally {
            /**
             * Delete temporary archive/export file
             */
            if ($shouldDeleteSource && $importsTmpDisk->exists($this->exportFileStoredPath)) {
                $importsTmpDisk->delete($this->exportFileStoredPath);
            }

            /**
             * Delete extraction directory
             */
            if (isset($extractedDir) && $importsTmpDisk->exists($extractedDir)) {
                $importsTmpDisk->deleteDirectory($extractedDir);
            }
        }
    }

    /**
     * Fallback for "Import to selected conversation" option when archive has additional media files that should be
     * merged into selected conversation
     *
     * @param ImportArchiveExtractorFactory $archiveExtractorsFactory
     * @param ParserRegistry                $parserRegistry
     * @param ImportedMediaResolverService  $importedMediaResolverService
     * @param MediaStorageInterface         $mediaStorage
     *
     * @return void
     */
    private function runMediaOnlyFallback(
        ImportArchiveExtractorFactory $archiveExtractorsFactory,
        ParserRegistry                $parserRegistry,
        ImportedMediaResolverService  $importedMediaResolverService,
        MediaStorageInterface         $mediaStorage
    ): void {
        /**
         * This fallback is only for "Import to selected conversation" scenario
         */
        if (!$this->strategy instanceof ImportOnlyMediaFilesStrategyInterface) {
            return;
        }

        $targetConversationId = $this->strategy->getImportMode()?->getTargetConversationId();
        if (!isset($targetConversationId)) {
            return;
        }

        $mediaUploadJob = new ProcessConversationMediaUpload(
            userId: $this->userId,
            conversationId: $targetConversationId,
            path: $this->exportFileStoredPath
        );

        $mediaUploadJob->handle(
            parserRegistry: $parserRegistry,
            importedMediaResolverService: $importedMediaResolverService,
            mediaStorage: $mediaStorage,
            archiveExtractorsFactory: $archiveExtractorsFactory
        );
    }
}
