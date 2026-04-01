<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\MediaAttachment;

use App\Models\MediaAttachment;
use App\Models\MediaTypes\SupportedMediaTypesEnum;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(MediaAttachment::class, 'toApiArray')]
final class ToApiArrayWhenStoredPathAbsentTest extends TestCase
{
    public function test_url_null_and_not_loaded_when_stored_path_null(): void
    {
        $attachment = new MediaAttachment([
            'stored_path'       => null,
            'export_path'       => 'only/export/path.jpg',
            'media_type'        => SupportedMediaTypesEnum::IMAGE->value,
            'mime_type'         => null,
            'original_filename' => 'path.jpg',
        ]);
        $attachment->id = 7;

        $payload = $attachment->toApiArray(3, 9);

        $this->assertNull($payload['url']);
        $this->assertFalse($payload['is_loaded']);
        $this->assertTrue($payload['is_image']);
    }

    public function test_url_null_when_stored_path_is_empty_string(): void
    {
        $attachment = new MediaAttachment([
            'stored_path'       => '',
            'export_path'       => 'x',
            'media_type'        => null,
            'mime_type'         => null,
            'original_filename' => null,
        ]);
        $attachment->id = 1;

        $payload = $attachment->toApiArray(1, 1);

        $this->assertNull($payload['url']);
        $this->assertFalse($payload['is_loaded']);
        $this->assertFalse($payload['is_image']);
    }
}
