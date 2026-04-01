<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Factories\ImportArchiveExtractorFactory;

use App\Services\Import\Archives\ImportArchiveExtractorInterface;
use App\Services\Import\Factories\ImportArchiveExtractorFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ImportArchiveExtractorFactory::class, 'makeForPath')]
final class MakeForPathTest extends TestCase
{
    public function test_returns_first_supporting_extractor(): void
    {
        $factory = new ImportArchiveExtractorFactory();
        $path    = 'imports/archive.zip';

        $first = $this->createMock(ImportArchiveExtractorInterface::class);
        $first->expects($this->once())->method('supports')->with($path)->willReturn(false);

        $second = $this->createMock(ImportArchiveExtractorInterface::class);
        $second->expects($this->once())->method('supports')->with($path)->willReturn(true);

        $third = $this->createMock(ImportArchiveExtractorInterface::class);
        $third->expects($this->never())->method('supports');

        $factory->register($first)->register($second)->register($third);

        $this->assertSame($second, $factory->makeForPath($path));
    }

    public function test_returns_null_when_no_extractor_supports_path(): void
    {
        $factory = new ImportArchiveExtractorFactory();
        $path    = 'imports/archive.7z';

        $extractor = $this->createMock(ImportArchiveExtractorInterface::class);
        $extractor->expects($this->once())->method('supports')->with($path)->willReturn(false);

        $factory->register($extractor);

        $this->assertNull($factory->makeForPath($path));
    }
}
