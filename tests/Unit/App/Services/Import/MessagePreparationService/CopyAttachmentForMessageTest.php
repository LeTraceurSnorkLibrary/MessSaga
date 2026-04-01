<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\MessagePreparationService;

use App\Services\Import\MessagePreparationService;
use App\Services\Media\MediaFileStorageService;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversMethod(MessagePreparationService::class, '__construct')]
#[CoversMethod(MessagePreparationService::class, 'copyAttachmentForMessage')]
final class CopyAttachmentForMessageTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_returns_null_when_media_root_missing_or_attachment_path_empty(): void
    {
        $storage = $this->createMock(MediaFileStorageService::class);
        $storage->expects($this->never())->method('copyForConversation');

        $service = new MessagePreparationService($storage);

        $this->assertNull($service->copyAttachmentForMessage(null, ['attachment_export_path' => 'x.jpg'], 1));
        $this->assertNull($service->copyAttachmentForMessage('/tmp/root', ['attachment_export_path' => ''], 1));
        $this->assertNull($service->copyAttachmentForMessage('/tmp/root', [], 1));
    }

    /**
     * @throws Exception
     */
    public function test_delegates_copy_to_media_storage_service(): void
    {
        $storage = $this->createMock(MediaFileStorageService::class);
        $storage->expects($this->once())
            ->method('copyForConversation')
            ->with('/tmp/root', 'img/photo.jpg', 77)
            ->willReturn('conversations/77/media/photo.jpg');

        $service = new MessagePreparationService($storage);

        $result = $service->copyAttachmentForMessage(
            '/tmp/root',
            ['attachment_export_path' => 'img/photo.jpg'],
            77
        );

        $this->assertSame('conversations/77/media/photo.jpg', $result);
    }
}
