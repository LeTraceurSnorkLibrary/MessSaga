<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;

use App\Services\Import\Export\Locators\ExportFile\AbstractExportFileLocator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversMethod(AbstractExportFileLocator::class, 'findRecursive')]
final class FindRecursiveTest extends TestCase
{
    public function test_returns_null_when_scandir_fails_or_directory_missing(): void
    {
        $proxy = new class extends AbstractExportFileLocator {
            public function locate(string $absoluteExtractedRoot): ?string
            {
                return null;
            }
        };

        $method = (new ReflectionClass($proxy))->getMethod('findRecursive');
        $method->setAccessible(true);

        $result = $method->invoke($proxy, '/root/that/does/not/exist', '', static fn (): bool => true);

        $this->assertNull($result);
    }
}
