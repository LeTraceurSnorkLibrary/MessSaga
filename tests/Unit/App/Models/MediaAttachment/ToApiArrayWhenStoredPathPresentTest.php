<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\MediaAttachment;

use App\Models\MediaAttachment;
use App\Models\MediaTypes\SupportedMediaTypesEnum;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(MediaAttachment::class, 'toApiArray')]
final class ToApiArrayWhenStoredPathPresentTest extends TestCase
{
    public function test_builds_url_and_loaded_flags_when_stored_path_non_empty(): void
    {
        $attachment = new MediaAttachment([
            'stored_path'       => 'conversations/1/media/x.bin',
            'export_path'       => 'export/x.bin',
            'media_type'        => SupportedMediaTypesEnum::VIDEO->value,
            'mime_type'         => 'video/mp4',
            'original_filename' => 'x.bin',
        ]);
        $attachment->id = 99;

        $payload = $attachment->toApiArray(5, 42);

        $this->assertSame(99, $payload['id']);
        $this->assertIsString($payload['url']);
        $this->assertStringContainsString('5', $payload['url']);
        $this->assertStringContainsString('42', $payload['url']);
        $this->assertSame('export/x.bin', $payload['export_path']);
        $this->assertSame(SupportedMediaTypesEnum::VIDEO->value, $payload['media_type']);
        $this->assertSame('video/mp4', $payload['mime_type']);
        $this->assertSame('x.bin', $payload['original_filename']);
        $this->assertTrue($payload['is_loaded']);
        $this->assertFalse($payload['is_image']);
    }

    public function test_is_image_true_when_media_type_is_image(): void
    {
        $attachment = new MediaAttachment([
            'stored_path'       => 'conversations/1/media/p.jpg',
            'export_path'       => 'p.jpg',
            'media_type'        => SupportedMediaTypesEnum::IMAGE->value,
            'mime_type'         => 'image/jpeg',
            'original_filename' => 'p.jpg',
        ]);
        $attachment->id = 1;

        $payload = $attachment->toApiArray(1, 2);

        $this->assertTrue($payload['is_image']);
    }
}
