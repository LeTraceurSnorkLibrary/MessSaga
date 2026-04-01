<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Media\MediaFileStorageService;

use App\Services\Media\MediaFileStorageService;
use PHPUnit\Framework\Attributes\CoversMethod;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

#[CoversMethod(MediaFileStorageService::class, 'findUniqueFileByBasename')]
#[CoversMethod(MediaFileStorageService::class, 'getBasenameIndex')]
final class FindUniqueFileByBasenameTest extends TestCase
{
    private string $rootDir;

    /**
     * @throws ReflectionException
     */
    public function test_returns_unique_match_when_found_once(): void
    {
        mkdir($this->rootDir . '/x', 0o775, true);
        $file = $this->rootDir . '/x/A B.jpg';
        file_put_contents($file, 'img');

        $service = new MediaFileStorageService();
        $method  = new ReflectionClass($service)->getMethod('findUniqueFileByBasename');
        $method->setAccessible(true);

        $found = $method->invoke($service, $this->rootDir, 'A B.jpg');

        $this->assertSame($file, $found);
    }

    /**
     * @throws ReflectionException
     */
    public function test_returns_null_for_ambiguous_invalid_or_missing_directory(): void
    {
        mkdir($this->rootDir . '/a', 0o775, true);
        mkdir($this->rootDir . '/b', 0o775, true);
        file_put_contents($this->rootDir . '/a/photo.jpg', '1');
        file_put_contents($this->rootDir . '/b/photo.jpg', '2');

        $service = new MediaFileStorageService();
        $method  = new ReflectionClass($service)->getMethod('findUniqueFileByBasename');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($service, $this->rootDir, 'photo.jpg'));
        $this->assertNull($method->invoke($service, $this->rootDir, '***'));
        $this->assertNull($method->invoke($service, $this->rootDir . '/missing', 'photo.jpg'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootDir = sys_get_temp_dir() . '/messsaga_media_unique_' . uniqid('', true);
        mkdir($this->rootDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->rootDir);
        parent::tearDown();
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
