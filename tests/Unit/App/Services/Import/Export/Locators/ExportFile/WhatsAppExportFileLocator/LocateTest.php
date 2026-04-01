<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\ExportFile\WhatsAppExportFileLocator;

use App\Services\Import\Export\Locators\ExportFile\WhatsAppExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(WhatsAppExportFileLocator::class, 'locate')]
final class LocateTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir() . '/messsaga_whatsapp_locator_' . uniqid('', true);
        mkdir($this->tempDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);

        parent::tearDown();
    }

    public function test_prefers_txt_with_whatsapp_in_name_case_insensitive(): void
    {
        $this->touchFile('nested/chat.txt');
        $this->touchFile('Nested/WHATSAPP Chat.txt');

        $locator = new WhatsAppExportFileLocator();

        $this->assertSame('Nested/WHATSAPP Chat.txt', $locator->locate($this->tempDir));
    }

    public function test_falls_back_to_any_txt_when_whatsapp_named_absent(): void
    {
        $this->touchFile('logs/history.txt');

        $locator = new WhatsAppExportFileLocator();

        $this->assertSame('logs/history.txt', $locator->locate($this->tempDir));
    }

    public function test_returns_null_for_non_directory_root(): void
    {
        $locator = new WhatsAppExportFileLocator();

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
