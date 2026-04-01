<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;

use App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

#[CoversMethod(AbstractExportFileLocator::class, 'findRecursive')]
final class FindRecursiveTest extends TestCase
{
    private string $tempDir;

    /**
     * @throws ReflectionException
     */
    public function test_returns_null_when_scandir_fails_or_directory_missing(): void
    {
        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }
        };

        $method = new ReflectionClass($proxy)->getMethod('findRecursive');
        $method->setAccessible(true);

        $result = $method->invoke($proxy, '/root/that/does/not/exist', '', static fn(): bool => true);

        $this->assertNull($result);
    }

    public function test_prioritizes_file_in_current_directory_before_deeper_match(): void
    {
        $this->touchFile('match.txt');
        $this->touchFile('nested/match.txt');

        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindRecursive(string $absoluteDir, string $relativePrefix, callable $predicate): ?string
            {
                return $this->findRecursive($absoluteDir, $relativePrefix, $predicate);
            }
        };

        $found = $proxy->callFindRecursive($this->tempDir, '', static fn(string $name): bool => $name === 'match.txt');

        $this->assertSame('match.txt', $found);
    }

    public function test_includes_relative_prefix_when_finding_file(): void
    {
        $this->touchFile('folder/target.json');

        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindRecursive(string $absoluteDir, string $relativePrefix, callable $predicate): ?string
            {
                return $this->findRecursive($absoluteDir, $relativePrefix, $predicate);
            }
        };

        $found = $proxy->callFindRecursive(
            $this->tempDir . '/folder',
            'prefix',
            static fn(string $name): bool => $name === 'target.json'
        );

        $this->assertSame('prefix/target.json', $found);
    }

    public function test_finds_match_via_directory_traversal_in_second_loop(): void
    {
        $this->touchFile('root-unrelated.log');
        $this->touchFile('nested/deeper/target.txt');

        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }

            public function callFindRecursive(string $absoluteDir, string $relativePrefix, callable $predicate): ?string
            {
                return $this->findRecursive($absoluteDir, $relativePrefix, $predicate);
            }
        };

        $found = $proxy->callFindRecursive(
            $this->tempDir,
            '',
            static fn(string $name): bool => $name === 'target.txt'
        );

        $this->assertSame('nested/deeper/target.txt', $found);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/messsaga_find_recursive_' . uniqid('', true);
        mkdir($this->tempDir, 0o775, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
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
