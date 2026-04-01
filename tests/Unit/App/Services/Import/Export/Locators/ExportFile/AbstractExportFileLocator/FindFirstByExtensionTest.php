<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;

use App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(AbstractExportFileLocator::class, 'findFirstByExtension')]
final class FindFirstByExtensionTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/messsaga_find_ext_' . uniqid('', true);
        mkdir($this->tempDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    public function test_finds_first_file_by_extension_with_or_without_leading_dot(): void
    {
        $this->touchFile('nested/chat.JSON');

        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindFirstByExtension(string $absoluteDir, string $extension): ?string
            {
                return $this->findFirstByExtension($absoluteDir, $extension);
            }
        };

        $this->assertSame('nested/chat.JSON', $proxy->callFindFirstByExtension($this->tempDir, 'json'));
        $this->assertSame('nested/chat.JSON', $proxy->callFindFirstByExtension($this->tempDir, '.json'));
    }

    public function test_returns_null_when_no_file_with_extension_exists(): void
    {
        $this->touchFile('note.txt');

        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindFirstByExtension(string $absoluteDir, string $extension): ?string
            {
                return $this->findFirstByExtension($absoluteDir, $extension);
            }
        };

        $this->assertNull($proxy->callFindFirstByExtension($this->tempDir, 'json'));
    }

    private function touchFile(string $relativePath): void
    {
        $fullPath = $this->tempDir . '/' . $relativePath;
        $dir      = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0o775, true);
        }

        file_put_contents($fullPath, 'data');
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        if ($items === false) {
            @rmdir($dir);

            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
