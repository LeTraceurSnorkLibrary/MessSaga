<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\MediaAttachment;
use App\Services\Media\Storage\MediaStorageInterface;
use Illuminate\Support\Facades\Log;
use Throwable;

class MediaAttachmentObserver
{
    /**
     * @param MediaStorageInterface $mediaStorage
     */
    public function __construct(
        private readonly MediaStorageInterface $mediaStorage
    ) {
    }

    /**
     * Обработчик события удаления медиа-вложения
     *
     * @param MediaAttachment $mediaAttachment
     *
     * @return void
     */
    public function deleting(MediaAttachment $mediaAttachment): void
    {
        $path = trim((string)($mediaAttachment->stored_path ?? ''));
        if ($path === '') {
            return;
        }

        try {
            if ($this->mediaStorage->exists($path)) {
                $this->mediaStorage->delete($path);
            }
        } catch (Throwable $e) {
            Log::warning('Failed to delete media file from storage', [
                'media_attachment_id' => $mediaAttachment->id,
                'path'                => $path,
                'error'               => $e->getMessage(),
            ]);
        }
    }
}
