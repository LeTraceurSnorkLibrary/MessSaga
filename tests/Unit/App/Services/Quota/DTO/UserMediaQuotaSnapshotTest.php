<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Quota\DTO;

use App\Services\Quota\DTO\UserMediaQuotaSnapshot;
use App\Tariffs\Tariff10;
use App\Tariffs\Tariff50;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(UserMediaQuotaSnapshot::class)]
final class UserMediaQuotaSnapshotTest extends TestCase
{
    public function test_to_array_contains_tariff_limits_and_remaining_values(): void
    {
        $snapshot = new UserMediaQuotaSnapshot(
            tariff: new Tariff10(),
            storageUsedBytes: 100,
            storageLimitBytes: 1_000,
            filesUsedCount: 3,
            filesLimitCount: 10,
        );

        $this->assertSame([
            'tariff' => [
                'code' => Tariff10::TARIFF_NAME,
                'label' => 'Тариф 10',
            ],
            'storage' => [
                'used' => 100,
                'limit' => 1_000,
                'remaining' => 900,
            ],
            'files' => [
                'used' => 3,
                'limit' => 10,
                'remaining' => 7,
            ],
        ], $snapshot->toArray());
    }

    public function test_remaining_values_are_clamped_to_zero_when_usage_exceeds_limit(): void
    {
        $snapshot = new UserMediaQuotaSnapshot(
            tariff: new Tariff50(),
            storageUsedBytes: 1_500,
            storageLimitBytes: 1_000,
            filesUsedCount: 12,
            filesLimitCount: 10,
        );

        $this->assertSame(0, $snapshot->getRemainingStorageBytes());
        $this->assertSame(0, $snapshot->getRemainingFilesCount());
    }

    public function test_block_reason_prioritizes_files_then_storage_and_none_when_allowed(): void
    {
        $filesExceeded = new UserMediaQuotaSnapshot(
            tariff: new Tariff10(),
            storageUsedBytes: 0,
            storageLimitBytes: 1_000,
            filesUsedCount: 10,
            filesLimitCount: 10,
        );
        $this->assertSame('quota_files_exceeded', $filesExceeded->getMediaUploadBlockReason());
        $this->assertFalse($filesExceeded->canUploadMedia());

        $storageExceeded = new UserMediaQuotaSnapshot(
            tariff: new Tariff10(),
            storageUsedBytes: 1_000,
            storageLimitBytes: 1_000,
            filesUsedCount: 1,
            filesLimitCount: 10,
        );
        $this->assertSame('quota_storage_exceeded', $storageExceeded->getMediaUploadBlockReason());
        $this->assertFalse($storageExceeded->canUploadMedia());

        $allowed = new UserMediaQuotaSnapshot(
            tariff: new Tariff10(),
            storageUsedBytes: 500,
            storageLimitBytes: 1_000,
            filesUsedCount: 2,
            filesLimitCount: 10,
        );
        $this->assertNull($allowed->getMediaUploadBlockReason());
        $this->assertTrue($allowed->canUploadMedia());
    }
}

