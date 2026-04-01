<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\MessagePreparationService;

use App\Services\Import\MessagePreparationService;
use App\Services\Media\MediaFileStorageService;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversMethod(MessagePreparationService::class, '__construct')]
#[CoversMethod(MessagePreparationService::class, 'normalizeExternalId')]
final class NormalizeExternalIdTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_returns_trimmed_string_for_scalar_values(): void
    {
        $service = new MessagePreparationService($this->createStub(MediaFileStorageService::class));

        $this->assertSame('abc', $service->normalizeExternalId('  abc  '));
        $this->assertSame('123', $service->normalizeExternalId(123));
        $this->assertSame('1', $service->normalizeExternalId(true));
    }

    /**
     * @throws Exception
     */
    public function test_returns_null_for_non_scalars_or_empty_after_trim(): void
    {
        $service = new MessagePreparationService($this->createStub(MediaFileStorageService::class));

        $this->assertNull($service->normalizeExternalId(['x']));
        $this->assertNull($service->normalizeExternalId((object)['x' => 1]));
        $this->assertNull($service->normalizeExternalId('   '));
        $this->assertNull($service->normalizeExternalId(null));
    }
}
