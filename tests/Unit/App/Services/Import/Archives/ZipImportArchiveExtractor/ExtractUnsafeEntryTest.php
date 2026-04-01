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
#[CoversMethod(ZipImportArchiveExtractor::class, 'extractSafely')]
final class ExtractUnsafeEntryTest extends TestCase
{
    public function test_extract_throws_when_zip_contains_unsafe_entry_path(): void
    {
        $zipRelativePath = 'imports/unsafe-entry.zip';
        $absoluteZipPath = Storage::path($zipRelativePath);

        $dir = dirname($absoluteZipPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0o775, true);
        }

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($absoluteZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true);
        $zip->addFromString('../escape.txt', 'unsafe');
        $zip->close();

        $service = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());

        $this->expectException(ArchiveExtractionFailedException::class);
        $this->expectExceptionMessage('ZIP archive contains unsafe entries or cannot be extracted');

        try {
            $service->extract($zipRelativePath, 'telegram');
        } finally {
            Storage::delete($zipRelativePath);
        }
    }
}
