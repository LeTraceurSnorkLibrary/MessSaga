<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\ZipImportArchiveExtractor;

use App\Services\Import\Archives\ZipImportArchiveExtractor;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ZipImportArchiveExtractor::class, 'supports')]
final class SupportsTest extends TestCase
{
    public function test_detects_zip_extension_case_insensitive(): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());

        $this->assertTrue($extractor->supports('imports/archive.ZIP'));
        $this->assertFalse($extractor->supports('imports/archive.rar'));
    }
}
