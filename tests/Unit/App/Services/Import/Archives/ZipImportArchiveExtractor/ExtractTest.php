<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\ZipImportArchiveExtractor;

use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;
use App\Services\Import\Archives\ZipImportArchiveExtractor;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(ZipImportArchiveExtractor::class, '__construct')]
#[CoversMethod(ZipImportArchiveExtractor::class, 'extract')]
#[CoversMethod(ZipImportArchiveExtractor::class, 'extractArchiveOnly')]
final class ExtractTest extends TestCase
{
    public function test_throws_when_zip_file_not_found(): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());

        $this->expectException(ArchiveExtractionFailedException::class);
        $this->expectExceptionMessage('ZIP file not found');

        $extractor->extract('imports/not-existing.zip', 'telegram');
    }
}
