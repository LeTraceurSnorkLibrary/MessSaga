<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Quota\TariffUserReassignmentService;

use App\Models\Tariff;
use App\Models\User;
use App\Services\Quota\TariffUserReassignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(TariffUserReassignmentService::class)]
final class ReassignUsersFromTariffCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_reassigns_users_to_selected_target_tariff(): void
    {
        Tariff::query()->create([
            'name'                  => 'from-plan',
            'label'                 => 'From',
            'price'                 => 100.00,
            'max_storage_mb'        => 10,
            'max_media_files_count' => 10,
        ]);
        Tariff::query()->create([
            'name'                  => 'to-plan',
            'label'                 => 'To',
            'price'                 => 200.00,
            'max_storage_mb'        => 20,
            'max_media_files_count' => 20,
        ]);

        $userA = User::factory()->create(['tariff_code' => 'from-plan']);
        $userB = User::factory()->create(['tariff_code' => 'from-plan']);
        $userC = User::factory()->create(['tariff_code' => 'to-plan']);

        $updated = app(TariffUserReassignmentService::class)->reassignUsersFromTariffCode(
            sourceTariffCode: 'from-plan',
            targetTariffCode: 'to-plan',
        );

        $this->assertSame(2, $updated);
        $this->assertSame('to-plan', $userA->fresh()?->tariff_code);
        $this->assertSame('to-plan', $userB->fresh()?->tariff_code);
        $this->assertSame('to-plan', $userC->fresh()?->tariff_code);
    }
}
