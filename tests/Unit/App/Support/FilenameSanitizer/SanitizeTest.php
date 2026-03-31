<?php

declare(strict_types=1);

namespace Tests\Unit\App\Support\FilenameSanitizer;

use App\Support\FilenameSanitizer;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(FilenameSanitizer::class, 'sanitize')]
final class SanitizeTest extends TestCase
{
    public function test_keeps_allowed_symbols_and_letters(): void
    {
        $result = FilenameSanitizer::sanitize('Report_2026-03-30.v1.txt');

        $this->assertSame('Report_2026-03-30.v1.txt', $result);
    }

    public function test_replaces_unsafe_characters_and_collapses_underscores(): void
    {
        $result = FilenameSanitizer::sanitize("a\tb   c###d");

        $this->assertSame('a_b_c_d', $result);
    }

    public function test_returns_file_when_name_becomes_empty(): void
    {
        $result = FilenameSanitizer::sanitize('***');

        $this->assertSame('file', $result);
    }
}
