<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\ZipImportArchiveExtractor;

use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;
use App\Services\Import\Archives\ZipImportArchiveExtractor;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(ZipImportArchiveExtractor::class, 'extract')]
#[CoversMethod(ZipImportArchiveExtractor::class, 'extractArchiveOnly')]
final class ExtractOpenFailureTest extends TestCase
{
    public function test_extract_throws_when_zip_cannot_be_opened(): void
    {
        $zipRelativePath = 'imports/not-a-zip.zip';
        Storage::put($zipRelativePath, 'plain text content');

        $service = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());

        $this->expectException(ArchiveExtractionFailedException::class);
        $this->expectExceptionMessage('Failed to open ZIP archive');

        try {
            $service->extract($zipRelativePath, 'telegram');
        } finally {
            Storage::delete($zipRelativePath);
        }
    }
}
