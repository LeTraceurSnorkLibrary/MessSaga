<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\DTO\ArchiveExtractionResult;

use App\Services\Import\Archives\DTO\ArchiveExtractionResult;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ArchiveExtractionResult::class, '__construct')]
#[CoversMethod(ArchiveExtractionResult::class, 'getExportFileAbsolutePath')]
#[CoversMethod(ArchiveExtractionResult::class, 'getMediaRootPath')]
#[CoversMethod(ArchiveExtractionResult::class, 'getExtractedDir')]
final class ConstructorGettersTest extends TestCase
{
    public function test_returns_passed_values(): void
    {
        $dto = new ArchiveExtractionResult('/tmp/export.json', '/tmp/media', 'chat_imports/extracted_1');

        $this->assertSame('/tmp/export.json', $dto->getExportFileAbsolutePath());
        $this->assertSame('/tmp/media', $dto->getMediaRootPath());
        $this->assertSame('chat_imports/extracted_1', $dto->getExtractedDir());
    }
}
