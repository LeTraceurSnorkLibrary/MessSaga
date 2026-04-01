<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\MediaTypes\SupportedMediaTypesEnum;

use App\Models\MediaTypes\SupportedMediaTypesEnum;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(SupportedMediaTypesEnum::class, 'detect')]
final class DetectUnknownMimeFallsBackToPathTest extends TestCase
{
    public function test_unknown_non_empty_mime_falls_through_to_path_extension(): void
    {
        $this->assertSame(
            SupportedMediaTypesEnum::AUDIO,
            SupportedMediaTypesEnum::detect('application/octet-stream', 'voice/file.mp3')
        );
    }

    public function test_mime_prefix_wins_over_conflicting_path_extension(): void
    {
        $this->assertSame(
            SupportedMediaTypesEnum::IMAGE,
            SupportedMediaTypesEnum::detect('image/jpeg', 'fake-name.mp4')
        );
    }
}
