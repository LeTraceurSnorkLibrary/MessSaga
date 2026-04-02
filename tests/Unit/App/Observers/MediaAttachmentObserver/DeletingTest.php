<?php

declare(strict_types=1);

namespace Tests\Unit\App\Observers\MediaAttachmentObserver;

use App\Models\MediaAttachment;
use App\Observers\MediaAttachmentObserver;
use App\Services\Media\Storage\MediaStorageInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(MediaAttachmentObserver::class)]
final class DeletingTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_deletes_object_from_storage_when_path_present_and_exists(): void
    {
        $storage = $this->createMock(MediaStorageInterface::class);
        $storage->expects($this->once())
            ->method('exists')
            ->with('conversations/1/media/a.jpg')
            ->willReturn(true);
        $storage->expects($this->once())
            ->method('delete')
            ->with('conversations/1/media/a.jpg')
            ->willReturn(true);

        $observer           = new MediaAttachmentObserver($storage);
        $model              = new MediaAttachment();
        $model->id          = 1;
        $model->stored_path = 'conversations/1/media/a.jpg';

        $observer->deleting($model);
    }

    /**
     * @throws Exception
     */
    public function test_skips_delete_when_stored_path_empty(): void
    {
        $storage = $this->createMock(MediaStorageInterface::class);
        $storage->expects($this->never())->method('exists');
        $storage->expects($this->never())->method('delete');

        $observer           = new MediaAttachmentObserver($storage);
        $model              = new MediaAttachment();
        $model->stored_path = '';

        $observer->deleting($model);
    }

    /**
     * @throws Exception
     */
    public function test_skips_delete_when_file_not_in_storage(): void
    {
        $storage = $this->createMock(MediaStorageInterface::class);
        $storage->expects($this->once())
            ->method('exists')
            ->willReturn(false);
        $storage->expects($this->never())->method('delete');

        $observer           = new MediaAttachmentObserver($storage);
        $model              = new MediaAttachment();
        $model->stored_path = 'conversations/1/media/missing.jpg';

        $observer->deleting($model);
    }
}
