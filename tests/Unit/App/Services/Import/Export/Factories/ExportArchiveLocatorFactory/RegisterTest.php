<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;

use App\Services\Import\Export\Factories\ExportArchiveLocatorFactory;
use App\Services\Import\Export\Locators\Archive\ExportArchiveLocatorInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ExportArchiveLocatorFactory::class, 'register')]
final class RegisterTest extends TestCase
{
    public function test_register_returns_same_factory_instance(): void
    {
        $factory = new ExportArchiveLocatorFactory();
        $locator = $this->createStub(ExportArchiveLocatorInterface::class);

        $this->assertSame($factory, $factory->register('telegram', $locator));
    }

    public function test_register_overwrites_locator_for_same_messenger_type(): void
    {
        $factory    = new ExportArchiveLocatorFactory();
        $oldLocator = $this->createStub(ExportArchiveLocatorInterface::class);
        $newLocator = $this->createStub(ExportArchiveLocatorInterface::class);

        $factory->register('whatsapp', $oldLocator);
        $factory->register('whatsapp', $newLocator);

        $this->assertSame($newLocator, $factory->make('whatsapp'));
    }
}
