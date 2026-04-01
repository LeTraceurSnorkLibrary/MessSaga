<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Archives\ZipImportArchiveExtractor;

use App\Services\Import\Archives\ZipImportArchiveExtractor;
use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ZipArchive;

#[CoversMethod(ZipImportArchiveExtractor::class, 'isSymlinkEntry')]
final class IsSymlinkEntryTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function test_returns_false_when_external_attributes_not_available(): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());
        $method    = new ReflectionClass($extractor)->getMethod('isSymlinkEntry');
        $method->setAccessible(true);

        $zip = $this->createStub(ZipArchive::class);
        $zip->method('getExternalAttributesIndex')->willReturn(false);

        $this->assertFalse($method->invoke($extractor, $zip, 0));
    }

    /**
     * @throws ReflectionException
     */
    public function test_returns_false_for_non_unix_entry_even_when_attributes_exist(): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());
        $method    = new ReflectionClass($extractor)->getMethod('isSymlinkEntry');
        $method->setAccessible(true);

        $zip = $this->createMock(ZipArchive::class);
        $zip->expects($this->once())
            ->method('getExternalAttributesIndex')
            ->willReturnCallback(static function (
                int $index,
                int &$opsys,
                int &$attr,
                int $flags
            ): bool {
                $opsys = 0; // not UNIX
                $attr  = 0xA000 << 16;

                return true;
            });

        $this->assertFalse($method->invoke($extractor, $zip, 0));
    }

    /**
     * @throws ReflectionException
     */
    public function test_returns_true_for_unix_symlink_entry(): void
    {
        $extractor = new ZipImportArchiveExtractor(new ExportArchiveLocatorFactory());
        $method    = new ReflectionClass($extractor)->getMethod('isSymlinkEntry');
        $method->setAccessible(true);

        $zip = $this->createMock(ZipArchive::class);
        $zip->expects($this->once())
            ->method('getExternalAttributesIndex')
            ->willReturnCallback(static function (
                int $index,
                int &$opsys,
                int &$attr,
                int $flags
            ): bool {
                $opsys = ZipArchive::OPSYS_UNIX;
                $attr  = 0xA000 << 16; // symlink mode bits

                return true;
            });

        $this->assertTrue($method->invoke($extractor, $zip, 0));
    }
}
