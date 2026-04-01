<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Media\MediaFileStorageService;

use App\Services\Media\ImportedMediaResolverService;
use App\Services\Media\Storage\MediaStorageInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

#[CoversMethod(ImportedMediaResolverService::class, 'tryResolveByExportPath')]
final class TryResolveByExportPathTest extends TestCase
{
    private string $rootDir;

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function test_resolves_valid_relative_path_inside_root(): void
    {
        mkdir($this->rootDir . '/x', 0o775, true);
        $file = $this->rootDir . '/x/a.jpg';
        file_put_contents($file, 'img');

        $service = new ImportedMediaResolverService($this->createStub(MediaStorageInterface::class));
        $method  = new ReflectionClass($service)->getMethod('tryResolveByExportPath');
        $method->setAccessible(true);

        $resolved = $method->invoke($service, $this->rootDir, 'x/a.jpg');

        $this->assertSame(realpath($file), $resolved);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function test_returns_null_for_traversal_or_invalid_or_missing_paths(): void
    {
        $service = new ImportedMediaResolverService($this->createStub(MediaStorageInterface::class));
        $method  = new ReflectionClass($service)->getMethod('tryResolveByExportPath');
        $method->setAccessible(true);

        $this->assertNull($method->invoke($service, $this->rootDir, '../secret.txt'));
        $this->assertNull($method->invoke($service, $this->rootDir, "bad\0name.jpg"));
        $this->assertNull($method->invoke($service, $this->rootDir, 'missing.jpg'));
        $this->assertNull($method->invoke($service, $this->rootDir, '   '));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->rootDir = sys_get_temp_dir() . '/messsaga_media_resolve_' . uniqid('', true);
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
