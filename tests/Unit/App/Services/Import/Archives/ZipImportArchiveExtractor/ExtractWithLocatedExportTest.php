<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\ZipImportArchiveExtractor;

use App\Services\Import\Archives\ZipImportArchiveExtractor;
use App\Services\Import\Export\DTO\ArchiveImportSource;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use App\Services\Import\Export\Locators\Archive\ExportArchiveLocatorInterface;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;
use ZipArchive;

#[CoversMethod(ZipImportArchiveExtractor::class, 'extract')]
#[CoversMethod(ZipImportArchiveExtractor::class, 'extractArchiveOnly')]
#[CoversMethod(ZipImportArchiveExtractor::class, 'guardArchiveLimits')]
#[CoversMethod(ZipImportArchiveExtractor::class, 'extractSafely')]
final class ExtractWithLocatedExportTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_extract_returns_export_and_media_paths_when_locator_finds_export(): void
    {
        $importsTmpDiskName = (string)config('filesystems.imports_tmp_disk', 'imports_tmp');
        $zipRelativePath    = 'imports/zip-with-export.zip';
        $absoluteZipPath    = Storage::path($zipRelativePath);
        $this->createZipFile($absoluteZipPath, [
            'result.json' => '{"messages":[]}',
            'media/a.jpg' => 'binary',
        ]);

        $locator = $this->createMock(ExportArchiveLocatorInterface::class);
        $locator->expects($this->once())
            ->method('locate')
            ->willReturnCallback(static function (string $extractedAbsoluteRoot): ArchiveImportSource {
                return new ArchiveImportSource('result.json', $extractedAbsoluteRoot . DIRECTORY_SEPARATOR . 'media');
            });

        $factory = new ExportArchiveLocatorFactory()->register('telegram', $locator);
        $service = new ZipImportArchiveExtractor($factory);

        $result = $service->extract($zipRelativePath, 'telegram');

        $this->assertNotNull($result->getExtractedDir());
        $this->assertNotNull($result->getExportFileAbsolutePath());
        $this->assertNotNull($result->getMediaRootPath());
        $this->assertFileExists($result->getExportFileAbsolutePath());
        $this->assertStringEndsWith(DIRECTORY_SEPARATOR . 'result.json', $result->getExportFileAbsolutePath());
        $this->assertStringEndsWith(DIRECTORY_SEPARATOR . 'media', $result->getMediaRootPath());

        $this->cleanupArtifacts($zipRelativePath, $result->getExtractedDir(), $importsTmpDiskName);
    }

    /**
     * @param array<string, string> $entries
     */
    private function createZipFile(string $absoluteZipPath, array $entries): void
    {
        $dir = dirname($absoluteZipPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0o775, true);
        }

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($absoluteZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
        foreach ($entries as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();
    }

    /**
     * @param string      $zipRelativePath
     * @param string|null $extractedDir
     * @param string      $importsTmpDiskName
     *
     * @return void
     */
    private function cleanupArtifacts(string $zipRelativePath, ?string $extractedDir, string $importsTmpDiskName): void
    {
        Storage::delete($zipRelativePath);
        if (is_string($extractedDir) && $extractedDir !== '') {
            Storage::disk($importsTmpDiskName)->deleteDirectory($extractedDir);
        }
    }
}
