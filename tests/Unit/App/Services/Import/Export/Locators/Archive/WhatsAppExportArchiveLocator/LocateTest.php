<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\Archive\WhatsAppExportArchiveLocator;

use App\Services\Import\Export\Locators\Archive\WhatsAppExportArchiveLocator;
use App\Services\Import\Export\Locators\ExportFile\WhatsAppExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(WhatsAppExportArchiveLocator::class, '__construct')]
#[CoversMethod(WhatsAppExportArchiveLocator::class, 'locate')]
final class LocateTest extends TestCase
{
    public function test_returns_null_when_export_file_not_found(): void
    {
        $fileLocator = $this->createMock(WhatsAppExportFileLocator::class);
        $fileLocator->expects($this->once())->method('locate')->with('/tmp/extracted')->willReturn(null);

        $locator = new WhatsAppExportArchiveLocator($fileLocator);

        $this->assertNull($locator->locate('/tmp/extracted'));
    }

    public function test_uses_extracted_root_as_media_path(): void
    {
        $fileLocator = $this->createStub(WhatsAppExportFileLocator::class);
        $fileLocator->method('locate')->willReturn('WhatsApp Chat with Bob.txt');

        $locator = new WhatsAppExportArchiveLocator($fileLocator);
        $source  = $locator->locate('/tmp/export');

        $this->assertNotNull($source);
        $this->assertSame('WhatsApp Chat with Bob.txt', $source->getExportFileRelativePath());
        $this->assertSame('/tmp/export', $source->getMediaRootAbsolutePath());
    }
}
