<?php

declare(strict_types=1);

namespace Tests\Unit\App\Tariffs;

use App\Models\Tariff;
use App\Tariffs\DatabaseTariff;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(DatabaseTariff::class)]
final class DatabaseTariffTest extends TestCase
{
    public function test_it_maps_eloquent_tariff_to_tariff_interface_contract(): void
    {
        $model = new Tariff([
            'name' => 'pro',
            'label' => 'Pro',
            'price' => 15.99,
            'max_storage_mb' => 200,
            'max_media_files_count' => 50,
        ]);

        $tariff = new DatabaseTariff($model);

        $this->assertSame('pro', $tariff->getName());
        $this->assertSame('Pro', $tariff->getLabel());
        $this->assertSame(200 * 1024 * 1024, $tariff->getMaxStorageBytes());
        $this->assertSame(50, $tariff->getMaxMediaFilesCount());
        $this->assertTrue($tariff->allowsMediaUpload());
    }

    public function test_it_disables_upload_when_any_limit_is_zero(): void
    {
        $model = new Tariff([
            'name' => 'limited',
            'label' => 'Limited',
            'price' => 1.00,
            'max_storage_mb' => 0,
            'max_media_files_count' => 10,
        ]);

        $tariff = new DatabaseTariff($model);

        $this->assertFalse($tariff->allowsMediaUpload());
    }
}

