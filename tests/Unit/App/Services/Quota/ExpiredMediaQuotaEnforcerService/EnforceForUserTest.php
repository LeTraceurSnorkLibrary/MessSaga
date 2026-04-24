<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Quota\ExpiredMediaQuotaEnforcerService;

use App\Models\Tariff;
use App\Models\User;
use App\Services\Media\Storage\MediaStorageInterface;
use App\Services\Quota\ExpiredMediaQuotaEnforcerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\TestCase;

#[CoversClass(ExpiredMediaQuotaEnforcerService::class)]
final class EnforceForUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_removes_newest_media_when_grace_expired_and_user_over_quota(): void
    {
        Tariff::query()->create([
            'name'                  => 'mini',
            'label'                 => 'Mini',
            'price'                 => 10.00,
            'max_storage_mb'        => 1,
            'max_media_files_count' => 1,
        ]);

        $user = User::factory()->create([
            'tariff_code' => 'mini',
            'media_quota_grace_until' => Carbon::now()->subHour(),
        ]);

        $conversationId = $this->seedConversationForUser($user->id);
        $olderAttachmentId = DB::table('media_attachments')->insertGetId([
            'conversation_id' => $conversationId,
            'stored_path' => 'media/older.jpg',
            'export_path' => 'older.jpg',
            'size_bytes' => 500_000,
            'created_at' => Carbon::now()->subMinutes(2),
            'updated_at' => Carbon::now()->subMinutes(2),
        ]);
        $newerAttachmentId = DB::table('media_attachments')->insertGetId([
            'conversation_id' => $conversationId,
            'stored_path' => 'media/newer.jpg',
            'export_path' => 'newer.jpg',
            'size_bytes' => 500_000,
            'created_at' => Carbon::now()->subMinute(),
            'updated_at' => Carbon::now()->subMinute(),
        ]);

        $storage = new class () implements MediaStorageInterface {
            /** @var list<string> */
            public array $deletedPaths = [];

            public function putStream(string $path, mixed $contents): bool
            {
                return true;
            }

            public function readStream(string $path): mixed
            {
                return false;
            }

            public function exists(string $path): bool
            {
                return true;
            }

            public function delete(string $path): bool
            {
                $this->deletedPaths[] = $path;

                return true;
            }

            public function mimeType(string $path): ?string
            {
                return null;
            }
        };
        $this->app->instance(MediaStorageInterface::class, $storage);

        $deleted = app(ExpiredMediaQuotaEnforcerService::class)->enforceForUser($user);

        $this->assertSame(1, $deleted);
        $this->assertSame(['media/newer.jpg'], $storage->deletedPaths);
        $this->assertNull(DB::table('media_attachments')->where('id', $newerAttachmentId)->value('stored_path'));
        $this->assertSame(0, (int)DB::table('media_attachments')->where('id', $newerAttachmentId)->value('size_bytes'));
        $this->assertSame('media/older.jpg', DB::table('media_attachments')->where('id', $olderAttachmentId)->value('stored_path'));
        $this->assertNull($user->fresh()->media_quota_grace_until);
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
