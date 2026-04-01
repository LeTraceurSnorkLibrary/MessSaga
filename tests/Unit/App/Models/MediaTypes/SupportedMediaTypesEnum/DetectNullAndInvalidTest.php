<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\MediaTypes\SupportedMediaTypesEnum;

use App\Models\MediaTypes\SupportedMediaTypesEnum;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(SupportedMediaTypesEnum::class, 'detect')]
final class DetectNullAndInvalidTest extends TestCase
{
    public function test_returns_null_when_mime_and_path_are_null(): void
    {
        $this->assertNull(SupportedMediaTypesEnum::detect(null, null));
    }

    public function test_returns_null_when_mime_empty_string_and_path_missing(): void
    {
        $this->assertNull(SupportedMediaTypesEnum::detect('', null));
        $this->assertNull(SupportedMediaTypesEnum::detect('  ', ''));
    }

    public function test_returns_null_for_unknown_mime_and_unknown_extension(): void
    {
        $this->assertNull(SupportedMediaTypesEnum::detect('application/pdf', 'doc.pdf'));
        $this->assertNull(SupportedMediaTypesEnum::detect(null, 'readme'));
        $this->assertNull(SupportedMediaTypesEnum::detect(null, 'file.xyz'));
    }
}
