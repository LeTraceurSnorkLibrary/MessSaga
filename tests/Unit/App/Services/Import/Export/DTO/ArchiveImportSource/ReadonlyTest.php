<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\DTO\ArchiveImportSource;

use App\Services\Import\Export\DTO\ArchiveImportSource;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversMethod(ArchiveImportSource::class, '__construct')]
final class ReadonlyTest extends TestCase
{
    public function test_properties_are_readonly(): void
    {
        $source     = new ArchiveImportSource('result.json', '/tmp/export');
        $reflection = new ReflectionClass($source);
        $property   = $reflection->getProperty('exportFileRelativePath');
        $property->setAccessible(true);

        $this->expectException(\Error::class);
        $property->setValue($source, 'changed.json');
    }
}
