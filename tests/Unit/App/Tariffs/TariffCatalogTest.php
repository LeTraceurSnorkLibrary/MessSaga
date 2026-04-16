<?php

declare(strict_types=1);

namespace Tests\Unit\App\Tariffs;

use App\Models\Tariff;
use App\Tariffs\DatabaseTariff;
use App\Tariffs\FreeTariff;
use App\Tariffs\TariffCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(TariffCatalog::class)]
#[CoversClass(DatabaseTariff::class)]
#[CoversClass(FreeTariff::class)]
final class TariffCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_returns_keyed_tariffs_with_expected_instances(): void
    {
        Tariff::query()->create([
            'name' => 'pro',
            'label' => 'Pro',
            'price' => 10.00,
            'max_storage_mb' => 1024,
            'max_media_files_count' => 100,
        ]);

        $all = TariffCatalog::all();

        $this->assertCount(2, $all);
        $this->assertInstanceOf(FreeTariff::class, $all[FreeTariff::TARIFF_NAME]);
        $this->assertInstanceOf(DatabaseTariff::class, $all['pro']);
    }

    public function test_for_code_falls_back_to_free_for_invalid_input(): void
    {
        $this->assertInstanceOf(FreeTariff::class, TariffCatalog::forCode(null));
        $this->assertInstanceOf(FreeTariff::class, TariffCatalog::forCode(''));
        $this->assertInstanceOf(FreeTariff::class, TariffCatalog::forCode('unknown'));
    }

    public function test_options_match_catalog_tariff_labels(): void
    {
        Tariff::query()->create([
            'name' => 'pro',
            'label' => 'Pro',
            'price' => 10.00,
            'max_storage_mb' => 1024,
            'max_media_files_count' => 100,
        ]);

        $options = TariffCatalog::options();

        $this->assertSame('Бесплатно', $options[FreeTariff::TARIFF_NAME]);
        $this->assertSame('Pro', $options['pro']);
    }

    public function test_for_code_returns_database_tariff_when_exists(): void
    {
        Tariff::query()->create([
            'name' => 'pro',
            'label' => 'Pro',
            'price' => 10.00,
            'max_storage_mb' => 1024,
            'max_media_files_count' => 100,
        ]);

        $tariff = TariffCatalog::forCode('pro');

        $this->assertSame('pro', $tariff->getName());
        $this->assertSame('Pro', $tariff->getLabel());
    }
}
