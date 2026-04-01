<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;

use App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(AbstractExportFileLocator::class, 'findExactName')]
final class FindExactNameTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/messsaga_find_exact_' . uniqid('', true);
        mkdir($this->tempDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    public function test_finds_file_by_exact_name_case_insensitive(): void
    {
        $this->touchFile('nested/Result.JSON');

        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindExactName(string $absoluteDir, string $exactName): ?string
            {
                return $this->findExactName($absoluteDir, $exactName);
            }
        };

        $this->assertSame('nested/Result.JSON', $proxy->callFindExactName($this->tempDir, 'result.json'));
    }

    public function test_returns_null_when_name_not_found(): void
    {
        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindExactName(string $absoluteDir, string $exactName): ?string
            {
                return $this->findExactName($absoluteDir, $exactName);
            }
        };

        $this->assertNull($proxy->callFindExactName($this->tempDir, 'missing.txt'));
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
