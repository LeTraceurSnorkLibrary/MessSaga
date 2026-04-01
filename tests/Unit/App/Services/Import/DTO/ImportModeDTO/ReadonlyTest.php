<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\DTO\ImportModeDTO;

use App\Services\Import\DTO\ImportModeDTO;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversMethod(ImportModeDTO::class, '__construct')]
final class ReadonlyTest extends TestCase
{
    public function test_properties_are_readonly(): void
    {
        $dto        = new ImportModeDTO('new', 77, 1);
        $reflection = new ReflectionClass($dto);
        $property   = $reflection->getProperty('mode');
        $property->setAccessible(true);

        $this->expectException(\Error::class);
        $property->setValue($dto, 'auto');
    }
}
