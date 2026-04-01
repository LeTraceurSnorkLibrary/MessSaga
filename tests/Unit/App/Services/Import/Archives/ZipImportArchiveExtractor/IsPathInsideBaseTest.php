<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\ZipImportArchiveExtractor;

use App\Services\Import\Archives\ZipImportArchiveExtractor;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

#[CoversMethod(ZipImportArchiveExtractor::class, 'isPathInsideBase')]
final class IsPathInsideBaseTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function test_returns_true_for_base_path_itself(): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());
        $method    = new ReflectionClass($extractor)->getMethod('isPathInsideBase');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($extractor, '/tmp/base', '/tmp/base'));
    }

    /**
     * @throws ReflectionException
     */
    public function test_returns_true_for_nested_path_inside_base(): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());
        $method    = new ReflectionClass($extractor)->getMethod('isPathInsideBase');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($extractor, '/tmp/base/sub/file', '/tmp/base'));
    }

    /**
     * @throws ReflectionException
     */
    public function test_returns_false_for_path_outside_base_or_non_string(): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());
        $method    = new ReflectionClass($extractor)->getMethod('isPathInsideBase');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($extractor, '/tmp/other', '/tmp/base'));
        $this->assertFalse($method->invoke($extractor, false, '/tmp/base'));
    }
}
