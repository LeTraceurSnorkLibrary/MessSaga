<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Factories\ImportArchiveExtractorFactory;

use App\Services\Import\Archives\ImportArchiveExtractorInterface;
use App\Services\Import\Factories\ImportArchiveExtractorFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversMethod(ImportArchiveExtractorFactory::class, 'register')]
final class RegisterTest extends TestCase
{
    public function test_register_adds_extractor_to_internal_registry_and_is_fluent(): void
    {
        $factory   = new ImportArchiveExtractorFactory();
        $extractor = $this->createStub(ImportArchiveExtractorInterface::class);

        $returned = $factory->register($extractor);

        $this->assertSame($factory, $returned);

        $reflection = new ReflectionClass($factory);
        $property   = $reflection->getProperty('preparers');
        $property->setAccessible(true);
        $registered = $property->getValue($factory);

        $this->assertCount(1, $registered);
        $this->assertSame($extractor, $registered[0]);
    }
}
