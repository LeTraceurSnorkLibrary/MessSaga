<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\MediaTypes\SupportedMediaTypesEnum;

use App\Models\MediaTypes\SupportedMediaTypesEnum;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(SupportedMediaTypesEnum::class, 'detect')]
final class DetectMimeTypePrefixesTest extends TestCase
{
    public function test_detects_image_from_mime_prefix(): void
    {
        $this->assertSame(
            SupportedMediaTypesEnum::IMAGE,
            SupportedMediaTypesEnum::detect('image/png')
        );
    }

    public function test_detects_audio_from_mime_prefix(): void
    {
        $this->assertSame(
            SupportedMediaTypesEnum::AUDIO,
            SupportedMediaTypesEnum::detect('audio/mpeg')
        );
    }

    public function test_detects_video_from_mime_prefix(): void
    {
        $this->assertSame(
            SupportedMediaTypesEnum::VIDEO,
            SupportedMediaTypesEnum::detect('video/mp4')
        );
    }
}
