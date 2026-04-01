<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\ZipImportArchiveExtractor;

use App\Services\Import\Archives\ZipImportArchiveExtractor;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

#[CoversMethod(ZipImportArchiveExtractor::class, 'isUnsafeZipEntryName')]
final class IsUnsafeZipEntryNameTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    #[DataProvider('unsafe_names_provider')]
    public function test_returns_true_for_unsafe_names(string $entryName): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());
        $method    = new ReflectionClass($extractor)->getMethod('isUnsafeZipEntryName');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($extractor, $entryName));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('safe_names_provider')]
    public function test_returns_false_for_safe_names(string $entryName): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());
        $method    = new ReflectionClass($extractor)->getMethod('isUnsafeZipEntryName');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($extractor, $entryName));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function unsafe_names_provider(): iterable
    {
        yield 'empty string' => [''];
        yield 'absolute unix' => ['/etc/passwd'];
        yield 'path traversal' => ['../../secret.txt'];
        yield 'windows drive' => ['C:\\Windows\\win.ini'];
        yield 'contains null byte' => ["file\0name.txt"];
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function safe_names_provider(): iterable
    {
        yield 'simple file' => ['chat.txt'];
        yield 'nested path' => ['export/media/photo.jpg'];
        yield 'windows separator but relative' => ['export\\media\\voice.ogg'];
    }
}
