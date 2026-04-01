<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\MediaTypes\SupportedMediaTypesEnum;

use App\Models\MediaTypes\SupportedMediaTypesEnum;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversMethod(SupportedMediaTypesEnum::class, 'detect')]
final class DetectPathExtensionTest extends TestCase
{
    #[DataProvider('image_extensions_provider')]
    public function test_detects_image_by_extension_when_mime_absent(
        string                  $path,
        SupportedMediaTypesEnum $expected
    ): void {
        $this->assertSame($expected, SupportedMediaTypesEnum::detect(null, $path));
    }

    #[DataProvider('audio_video_extensions_provider')]
    public function test_detects_audio_or_video_by_extension_when_mime_absent(
        string                  $path,
        SupportedMediaTypesEnum $expected
    ): void {
        $this->assertSame($expected, SupportedMediaTypesEnum::detect(null, $path));
    }

    public function test_normalizes_backslashes_for_extension(): void
    {
        $this->assertSame(
            SupportedMediaTypesEnum::IMAGE,
            SupportedMediaTypesEnum::detect(null, 'folder\\sub\\pic.PNG')
        );
    }

    /**
     * @return iterable<string, array{0: string, 1: SupportedMediaTypesEnum}>
     */
    public static function image_extensions_provider(): iterable
    {
        yield 'jpeg' => ['x.jpeg', SupportedMediaTypesEnum::IMAGE];
        yield 'jpg' => ['dir/x.JPG', SupportedMediaTypesEnum::IMAGE];
        yield 'png' => ['a.png', SupportedMediaTypesEnum::IMAGE];
        yield 'webp' => ['b.webp', SupportedMediaTypesEnum::IMAGE];
        yield 'heic' => ['c.heic', SupportedMediaTypesEnum::IMAGE];
    }

    /**
     * @return iterable<string, array{0: string, 1: SupportedMediaTypesEnum}>
     */
    public static function audio_video_extensions_provider(): iterable
    {
        yield 'mp3' => ['s.mp3', SupportedMediaTypesEnum::AUDIO];
        yield 'm4a' => ['s.m4a', SupportedMediaTypesEnum::AUDIO];
        yield 'opus' => ['s.opus', SupportedMediaTypesEnum::AUDIO];
        yield 'mp4' => ['v.mp4', SupportedMediaTypesEnum::VIDEO];
        yield 'webm_video' => ['v.webm', SupportedMediaTypesEnum::VIDEO];
    }
}
