<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\Archive\TelegramExportArchiveLocator;

use App\Services\Import\Export\Locators\Archive\TelegramExportArchiveLocator;
use App\Services\Import\Export\Locators\ExportFile\TelegramExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(TelegramExportArchiveLocator::class, '__construct')]
#[CoversMethod(TelegramExportArchiveLocator::class, 'locate')]
final class LocateTest extends TestCase
{
    public function test_returns_null_when_export_file_not_found(): void
    {
        $fileLocator = $this->createMock(TelegramExportFileLocator::class);
        $fileLocator->expects($this->once())->method('locate')->with('/tmp/extracted')->willReturn(null);

        $locator = new TelegramExportArchiveLocator($fileLocator);

        $this->assertNull($locator->locate('/tmp/extracted'));
    }

    public function test_uses_root_as_media_path_for_export_in_root(): void
    {
        $fileLocator = $this->createStub(TelegramExportFileLocator::class);
        $fileLocator->method('locate')->willReturn('result.json');

        $locator = new TelegramExportArchiveLocator($fileLocator);
        $source  = $locator->locate('/tmp/export');

        $this->assertNotNull($source);
        $this->assertSame('result.json', $source->getExportFileRelativePath());
        $this->assertSame('/tmp/export', $source->getMediaRootAbsolutePath());
    }

    public function test_builds_media_root_for_nested_export_file(): void
    {
        $fileLocator = $this->createStub(TelegramExportFileLocator::class);
        $fileLocator->method('locate')->willReturn('nested/chat/result.json');

        $locator = new TelegramExportArchiveLocator($fileLocator);
        $source  = $locator->locate('/tmp/export');

        $this->assertNotNull($source);
        $this->assertSame('nested/chat/result.json', $source->getExportFileRelativePath());
        $this->assertSame('/tmp/export' . DIRECTORY_SEPARATOR . 'nested' . DIRECTORY_SEPARATOR . 'chat', $source->getMediaRootAbsolutePath());
    }
}
