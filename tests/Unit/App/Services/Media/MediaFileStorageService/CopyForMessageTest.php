<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Media\MediaFileStorageService;

use App\Services\Media\ImportedMediaResolverService;
use App\Services\Media\Storage\LaravelMediaStorage;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(ImportedMediaResolverService::class, 'copyForMessage')]
#[CoversMethod(ImportedMediaResolverService::class, 'resolveSource')]
#[CoversMethod(ImportedMediaResolverService::class, 'extractCandidateBasenames')]
#[CoversMethod(ImportedMediaResolverService::class, 'findUniqueFileByBasename')]
#[CoversMethod(ImportedMediaResolverService::class, 'getBasenameIndex')]
final class CopyForMessageTest extends TestCase
{
    private string $rootDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootDir = sys_get_temp_dir() . '/messsaga_media_copy_msg_' . uniqid('', true);
        mkdir($this->rootDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->rootDir);
        Storage::deleteDirectory('conversations/202');
        parent::tearDown();
    }

    public function test_copies_file_by_legacy_basename_fallback(): void
    {
        $mediaDir = $this->rootDir . '/media';
        mkdir($mediaDir, 0o775, true);
        file_put_contents($mediaDir . '/photo.jpg', 'img-bytes');

        $service = new ImportedMediaResolverService(new LaravelMediaStorage(Storage::disk('local')));
        $stored  = $service->copyForMessage($this->rootDir, '001_ABC_photo.jpg', 202, 7);

        $this->assertSame('conversations/202/media/7/photo.jpg', $stored);
        $this->assertNotNull($stored);
        $this->assertTrue(Storage::exists($stored));
    }

    public function test_returns_null_when_legacy_basename_is_ambiguous(): void
    {
        mkdir($this->rootDir . '/a', 0o775, true);
        mkdir($this->rootDir . '/b', 0o775, true);
        file_put_contents($this->rootDir . '/a/photo.jpg', 'img-a');
        file_put_contents($this->rootDir . '/b/photo.jpg', 'img-b');

        $service = new ImportedMediaResolverService(new LaravelMediaStorage(Storage::disk('local')));
        $stored  = $service->copyForMessage($this->rootDir, '001_photo.jpg', 202, 7);

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
