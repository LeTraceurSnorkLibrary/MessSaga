<?php

declare(strict_types=1);

namespace Tests\Unit\App\Tariffs;

use App\Tariffs\FreeTariff;
use App\Tariffs\Tariff10;
use App\Tariffs\Tariff200;
use App\Tariffs\Tariff50;
use App\Tariffs\TariffCatalog;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(TariffCatalog::class)]
#[CoversClass(FreeTariff::class)]
#[CoversClass(Tariff10::class)]
#[CoversClass(Tariff50::class)]
#[CoversClass(Tariff200::class)]
final class TariffCatalogTest extends TestCase
{
    public function test_all_returns_keyed_tariffs_with_expected_instances(): void
    {
        $all = TariffCatalog::all();

        $this->assertCount(4, $all);
        $this->assertInstanceOf(FreeTariff::class, $all[FreeTariff::TARIFF_NAME]);
        $this->assertInstanceOf(Tariff10::class, $all[Tariff10::TARIFF_NAME]);
        $this->assertInstanceOf(Tariff50::class, $all[Tariff50::TARIFF_NAME]);
        $this->assertInstanceOf(Tariff200::class, $all[Tariff200::TARIFF_NAME]);
    }

    public function test_for_code_falls_back_to_free_for_invalid_input(): void
    {
        $this->assertInstanceOf(FreeTariff::class, TariffCatalog::forCode(null));
        $this->assertInstanceOf(FreeTariff::class, TariffCatalog::forCode(''));
        $this->assertInstanceOf(FreeTariff::class, TariffCatalog::forCode('unknown'));
    }

    public function test_options_match_catalog_tariff_labels(): void
    {
        $options = TariffCatalog::options();

        $this->assertSame('Бесплатно', $options[FreeTariff::TARIFF_NAME]);
        $this->assertSame('Тариф 10', $options[Tariff10::TARIFF_NAME]);
        $this->assertSame('Тариф 50', $options[Tariff50::TARIFF_NAME]);
        $this->assertSame('Тариф 200', $options[Tariff200::TARIFF_NAME]);
    }

    public function test_allows_media_upload_depends_on_positive_limits(): void
    {
        $this->assertFalse(new FreeTariff()->allowsMediaUpload());
        $this->assertTrue(new Tariff10()->allowsMediaUpload());
        $this->assertTrue(new Tariff50()->allowsMediaUpload());
        $this->assertTrue(new Tariff200()->allowsMediaUpload());
    }
}
