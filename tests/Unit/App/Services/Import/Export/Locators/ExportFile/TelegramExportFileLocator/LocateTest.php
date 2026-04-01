<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\ExportFile\TelegramExportFileLocator;

use App\Services\Import\Export\Locators\ExportFile\TelegramExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(TelegramExportFileLocator::class, 'locate')]
final class LocateTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/messsaga_telegram_locator_' . uniqid('', true);
        mkdir($this->tempDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);

        parent::tearDown();
    }

    public function test_prefers_exact_result_json_over_any_json(): void
    {
        $this->touchFile('nested/other.json');
        $this->touchFile('result.json');

        $locator = new TelegramExportFileLocator();

        $this->assertSame('result.json', $locator->locate($this->tempDir));
    }

    public function test_falls_back_to_first_json_when_result_json_absent(): void
    {
        $this->touchFile('nested/dialog.json');

        $locator = new TelegramExportFileLocator();

        $this->assertSame('nested/dialog.json', $locator->locate($this->tempDir));
    }

    public function test_returns_null_for_non_directory_root(): void
    {
        $locator = new TelegramExportFileLocator();

        $this->assertNull($locator->locate($this->tempDir . '/missing'));
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
