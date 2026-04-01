<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Media\MediaFileStorageService;

use App\Services\Media\MediaFileStorageService;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(MediaFileStorageService::class, 'copyForConversation')]
#[CoversMethod(MediaFileStorageService::class, 'resolveSource')]
#[CoversMethod(MediaFileStorageService::class, 'tryResolveByExportPath')]
#[CoversMethod(MediaFileStorageService::class, 'storeStream')]
final class CopyForConversationTest extends TestCase
{
    private string $rootDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootDir = sys_get_temp_dir() . '/messsaga_media_copy_conv_' . uniqid('', true);
        mkdir($this->rootDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->rootDir);
        Storage::deleteDirectory('conversations/101');
        parent::tearDown();
    }

    public function test_copies_file_by_exact_export_path(): void
    {
        $sourceDir = $this->rootDir . '/nested';
        mkdir($sourceDir, 0o775, true);
        file_put_contents($sourceDir . '/a.jpg', 'img-bytes');

        $service = new MediaFileStorageService();
        $stored  = $service->copyForConversation($this->rootDir, 'nested/a.jpg', 101);

        $this->assertSame('conversations/101/media/a.jpg', $stored);
        $this->assertNotNull($stored);
        $this->assertTrue(Storage::exists($stored));
    }

    public function test_returns_null_when_source_not_found(): void
    {
        $service = new MediaFileStorageService();

        $stored = $service->copyForConversation($this->rootDir, 'missing/file.jpg', 101);

        $this->assertNull($stored);
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
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
