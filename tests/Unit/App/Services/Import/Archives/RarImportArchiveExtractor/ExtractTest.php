<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\RarImportArchiveExtractor;

use App\Services\Import\Archives\Exceptions\ArchiveExtractionFailedException;
use App\Services\Import\Archives\RarImportArchiveExtractor;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(RarImportArchiveExtractor::class, 'extract')]
final class ExtractTest extends TestCase
{
    public function test_throws_not_supported_exception(): void
    {
        $extractor = new RarImportArchiveExtractor();

        $this->expectException(ArchiveExtractionFailedException::class);

        $extractor->extract('imports/messages.rar', 'telegram');
    }
}
