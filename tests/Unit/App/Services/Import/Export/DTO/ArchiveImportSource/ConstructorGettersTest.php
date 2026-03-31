<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\DTO\ArchiveImportSource;

use App\Services\Import\Export\DTO\ArchiveImportSource;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ArchiveImportSource::class, '__construct')]
#[CoversMethod(ArchiveImportSource::class, 'getExportFileRelativePath')]
#[CoversMethod(ArchiveImportSource::class, 'getMediaRootAbsolutePath')]
final class ConstructorGettersTest extends TestCase
{
    public function test_returns_constructor_values(): void
    {
        $source = new ArchiveImportSource('nested/result.json', '/tmp/export/nested');

        $this->assertSame('nested/result.json', $source->getExportFileRelativePath());
        $this->assertSame('/tmp/export/nested', $source->getMediaRootAbsolutePath());
    }
}
