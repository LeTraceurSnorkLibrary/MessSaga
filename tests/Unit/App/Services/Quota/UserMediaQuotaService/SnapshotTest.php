<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Quota\UserMediaQuotaService;

use App\Models\Tariff;
use App\Models\User;
use App\Services\Quota\UserMediaQuotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(UserMediaQuotaService::class)]
final class SnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_counts_only_stored_media_for_requested_user(): void
    {
        $tariff = Tariff::query()->create([
            'name'                  => 'pro',
            'label'                 => 'Pro',
            'price'                 => 10.00,
            'max_storage_mb'        => 1024,
            'max_media_files_count' => 100,
        ]);

        $user        = User::factory()->create([
            'tariff_code' => $tariff->name,
        ]);
        $anotherUser = User::factory()->create();

        $accountId      = DB::table('messenger_accounts')->insertGetId([
            'user_id'    => $user->id,
            'type'       => 'telegram',
            'name'       => 'Main',
            'meta'       => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $conversationId = DB::table('conversations')->insertGetId([
            'messenger_account_id' => $accountId,
            'external_id'          => 'conv-1',
            'title'                => 'Conversation',
            'participants'         => json_encode([]),
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        DB::table('media_attachments')->insert([
            [
                'conversation_id' => $conversationId,
                'stored_path'     => 'media/a.jpg',
                'export_path'     => 'a.jpg',
                'size_bytes'      => 120,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'conversation_id' => $conversationId,
                'stored_path'     => null,
                'export_path'     => 'missing.jpg',
                'size_bytes'      => 900,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
            [
                'conversation_id' => $conversationId,
                'stored_path'     => '',
                'export_path'     => 'empty.jpg',
                'size_bytes'      => 700,
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);

        $otherAccountId      = DB::table('messenger_accounts')->insertGetId([
            'user_id'    => $anotherUser->id,
            'type'       => 'telegram',
            'name'       => 'Other',
            'meta'       => json_encode([]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherConversationId = DB::table('conversations')->insertGetId([
            'messenger_account_id' => $otherAccountId,
            'external_id'          => 'conv-2',
            'title'                => 'Other conversation',
            'participants'         => json_encode([]),
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);
        DB::table('media_attachments')->insert([
            'conversation_id' => $otherConversationId,
            'stored_path'     => 'media/other.jpg',
            'export_path'     => 'other.jpg',
            'size_bytes'      => 500,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        $snapshot = app(UserMediaQuotaService::class)->snapshot($user);

        $this->assertSame(120, $snapshot->getStorageUsedBytes());
        $this->assertSame(1, $snapshot->getFilesUsedCount());
        $this->assertSame(1024 * 1024 * 1024, $snapshot->getStorageLimitBytes());
        $this->assertSame(100, $snapshot->getFilesLimitCount());
    }
}
