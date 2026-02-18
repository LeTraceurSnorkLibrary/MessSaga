<?php

namespace App\Jobs;

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

    public function __construct(
        public int $userId,
        public string $messengerType,
        public string $path,
        public ?int $conversationId = null,
    ) {
    }

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
                conversationId: $this->conversationId,
            );
        } finally {
            /**
             * Удаляем файл после обработки
             */
            if (Storage::exists($this->path)) {
                Storage::delete($this->path);
            }
        }
    }
}
