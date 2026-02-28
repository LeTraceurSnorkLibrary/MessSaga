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
use Illuminate\Support\Facades\Storage;

class ProcessChatImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int                     $userId
     * @param string                  $messengerType
     * @param string                  $path
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
     * @return void
     */
    public function handle(): void
    {
        try {
            /**
             * @var ImportService $service
             */
            $service = app(ImportService::class);

            $service->import(
                userId: $this->userId,
                messengerType: $this->messengerType,
                path: $this->path,
                strategy: $this->strategy
            );
        } finally {
            /**
             * Delete file after handling it
             */
            if (Storage::exists($this->path)) {
                Storage::delete($this->path);
            }
        }
    }
}
