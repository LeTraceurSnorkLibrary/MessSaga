<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\RarImportArchiveExtractor;

use App\Services\Import\Archives\RarImportArchiveExtractor;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(RarImportArchiveExtractor::class, 'supports')]
final class SupportsTest extends TestCase
{
    public function test_detects_rar_extension_case_insensitive(): void
    {
        $extractor = new RarImportArchiveExtractor();

        $this->assertTrue($extractor->supports('imports/messages.RAR'));
        $this->assertFalse($extractor->supports('imports/messages.zip'));
    }
}
