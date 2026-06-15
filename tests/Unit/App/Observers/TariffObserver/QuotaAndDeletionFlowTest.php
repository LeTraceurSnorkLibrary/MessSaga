<?php

declare(strict_types=1);

namespace Tests\Unit\App\Observers\TariffObserver;

use App\Models\Tariff;
use App\Models\User;
use App\Observers\TariffObserver;
use App\Tariffs\FreeTariff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(TariffObserver::class)]
final class QuotaAndDeletionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sets_grace_period_for_users_when_tariff_limits_are_downgraded(): void
    {
        $tariff = Tariff::query()->create([
            'name'                  => 'pro',
            'label'                 => 'Pro',
            'price'                 => 100.00,
            'max_storage_mb'        => 10,
            'max_media_files_count' => 10,
        ]);

        $user = User::factory()->create([
            'tariff_code' => 'pro',
        ]);

        $conversationId = $this->seedConversationForUser($user->id);
        DB::table('media_attachments')->insert([
            'conversation_id' => $conversationId,
            'stored_path'     => 'media/large.jpg',
            'export_path'     => 'large.jpg',
            'size_bytes'      => 2_000_000,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $tariff->max_storage_mb = 1;
        $tariff->save();

        $this->assertNotNull($user->fresh()->media_quota_grace_until);
    }

    public function test_moves_users_to_free_tariff_when_tariff_is_deleted(): void
    {
        $tariff = Tariff::query()->create([
            'name'                  => 'starter',
            'label'                 => 'Starter',
            'price'                 => 50.00,
            'max_storage_mb'        => 5,
            'max_media_files_count' => 5,
        ]);

        $user = User::factory()->create([
            'tariff_code' => 'starter',
        ]);

        $conversationId = $this->seedConversationForUser($user->id);
        DB::table('media_attachments')->insert([
            'conversation_id' => $conversationId,
            'stored_path'     => 'media/1.jpg',
            'export_path'     => '1.jpg',
            'size_bytes'      => 100_000,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $tariff->delete();

        $freshUser = $user->fresh();
        $this->assertSame(FreeTariff::TARIFF_NAME, $freshUser?->tariff_code);
        $this->assertNotNull($freshUser?->media_quota_grace_until);
        $this->assertTrue(
            Carbon::parse((string)$freshUser?->media_quota_grace_until)->between(
                now()->addDays(6)->startOfMinute(),
                now()->addDays(7)->endOfMinute(),
            ),
        );
    }

    private function seedConversationForUser(int $userId): int
    {
        $accountId = DB::table('messenger_accounts')->insertGetId([
            'user_id'    => $userId,
            'type'       => 'telegram',
            'name'       => 'Main',
            'meta'       => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('conversations')->insertGetId([
            'messenger_account_id' => $accountId,
            'external_id'          => 'conv-' . $userId,
            'title'                => 'Conversation',
            'participants'         => json_encode([]),
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
    }
}
