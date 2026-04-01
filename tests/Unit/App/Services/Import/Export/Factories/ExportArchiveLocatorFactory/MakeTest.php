<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;

use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use App\Services\Import\Export\Locators\Archive\ExportArchiveLocatorInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversMethod(ExportArchiveLocatorFactory::class, 'make')]
final class MakeTest extends TestCase
{
    public function test_make_returns_registered_locator(): void
    {
        $factory = new ExportArchiveLocatorFactory();
        $locator = $this->createStub(ExportArchiveLocatorInterface::class);
        $factory->register('telegram', $locator);

        $this->assertSame($locator, $factory->make('telegram'));
    }

    public function test_make_throws_for_unknown_messenger_type(): void
    {
        $factory = new ExportArchiveLocatorFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No ExportArchiveLocator registered for messenger type: signal');

        $factory->make('signal');
    }
}
