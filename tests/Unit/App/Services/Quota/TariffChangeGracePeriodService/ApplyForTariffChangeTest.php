<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Quota\TariffChangeGracePeriodService;

use App\Models\Tariff;
use App\Models\User;
use App\Services\Quota\TariffChangeGracePeriodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(TariffChangeGracePeriodService::class)]
final class ApplyForTariffChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_sets_grace_period_when_downgrade_exceeds_new_quota(): void
    {
        Tariff::query()->create([
            'name'                  => 'pro',
            'label'                 => 'Pro',
            'price'                 => 100.00,
            'max_storage_mb'        => 1024,
            'max_media_files_count' => 100,
        ]);
        Tariff::query()->create([
            'name'                  => 'mini',
            'label'                 => 'Mini',
            'price'                 => 10.00,
            'max_storage_mb'        => 1,
            'max_media_files_count' => 1,
        ]);

        $user = User::factory()->create([
            'tariff_code' => 'mini',
        ]);

        $conversationId = $this->seedConversationForUser($user->id);
        DB::table('media_attachments')->insert([
            [
                'conversation_id' => $conversationId,
                'stored_path' => 'media/1.jpg',
                'export_path' => '1.jpg',
                'size_bytes' => 900_000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'conversation_id' => $conversationId,
                'stored_path' => 'media/2.jpg',
                'export_path' => '2.jpg',
                'size_bytes' => 800_000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        app(TariffChangeGracePeriodService::class)->applyForTariffChange(
            user: $user,
            oldTariffCode: 'pro',
        );

        $this->assertNotNull($user->media_quota_grace_until);
        $this->assertTrue(
            Carbon::parse((string)$user->media_quota_grace_until)->between(
                now()->addDays(6)->startOfMinute(),
                now()->addDays(7)->endOfMinute()
            )
        );
    }

    public function test_does_not_set_grace_when_downgrade_still_within_quota(): void
    {
        Tariff::query()->create([
            'name'                  => 'pro',
            'label'                 => 'Pro',
            'price'                 => 100.00,
            'max_storage_mb'        => 1024,
            'max_media_files_count' => 100,
        ]);
        Tariff::query()->create([
            'name'                  => 'mini',
            'label'                 => 'Mini',
            'price'                 => 10.00,
            'max_storage_mb'        => 5,
            'max_media_files_count' => 5,
        ]);

        $user = User::factory()->create([
            'tariff_code' => 'mini',
        ]);

        $conversationId = $this->seedConversationForUser($user->id);
        DB::table('media_attachments')->insert([
            'conversation_id' => $conversationId,
            'stored_path' => 'media/1.jpg',
            'export_path' => '1.jpg',
            'size_bytes' => 100_000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(TariffChangeGracePeriodService::class)->applyForTariffChange(
            user: $user,
            oldTariffCode: 'pro',
        );

        $this->assertNull($user->media_quota_grace_until);
    }

    private function seedConversationForUser(int $userId): int
    {
        $accountId = DB::table('messenger_accounts')->insertGetId([
            'user_id' => $userId,
            'type' => 'telegram',
            'name' => 'Main',
            'meta' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('conversations')->insertGetId([
            'messenger_account_id' => $accountId,
            'external_id' => 'conv-' . $userId,
            'title' => 'Conversation',
            'participants' => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
