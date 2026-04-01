<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;

use App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(AbstractExportFileLocator::class, 'findFirstTxtContaining')]
final class FindFirstTxtContainingTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/messsaga_find_txt_' . uniqid('', true);
        mkdir($this->tempDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    public function test_finds_txt_file_containing_needle_case_insensitive(): void
    {
        $this->touchFile('nested/WHATSAPP chat.TXT');
        $this->touchFile('nested/whatsapp-chat.json');

        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindFirstTxtContaining(string $absoluteDir, string $needle): ?string
            {
                return $this->findFirstTxtContaining($absoluteDir, $needle);
            }
        };

        $this->assertSame('nested/WHATSAPP chat.TXT', $proxy->callFindFirstTxtContaining($this->tempDir, 'whatsapp'));
    }

    public function test_returns_null_when_only_non_txt_contains_needle(): void
    {
        $this->touchFile('nested/whatsapp-chat.json');

        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindFirstTxtContaining(string $absoluteDir, string $needle): ?string
            {
                return $this->findFirstTxtContaining($absoluteDir, $needle);
            }
        };

        $this->assertNull($proxy->callFindFirstTxtContaining($this->tempDir, 'whatsapp'));
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
