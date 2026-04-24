<?php

declare(strict_types=1);

namespace App\Services\Quota;

use App\Models\User;
use App\Services\Quota\DTO\UserMediaQuotaSnapshot;
use Illuminate\Support\Facades\DB;

class UserMediaQuotaService
{
    /**
     * @param User $user
     *
     * @return UserMediaQuotaSnapshot
     */
    public function snapshot(User $user): UserMediaQuotaSnapshot
    {
        $storageUsedBytes = (int)DB::table('media_attachments')
            ->join('conversations', 'conversations.id', '=', 'media_attachments.conversation_id')
            ->join('messenger_accounts', 'messenger_accounts.id', '=', 'conversations.messenger_account_id')
            ->where('messenger_accounts.user_id', $user->id)
            ->whereNotNull('media_attachments.stored_path')
            ->where('media_attachments.stored_path', '!=', '')
            ->sum('media_attachments.size_bytes');

        $filesUsedCount = DB::table('media_attachments')
            ->join('conversations', 'conversations.id', '=', 'media_attachments.conversation_id')
            ->join('messenger_accounts', 'messenger_accounts.id', '=', 'conversations.messenger_account_id')
            ->where('messenger_accounts.user_id', $user->id)
            ->whereNotNull('media_attachments.stored_path')
            ->where('media_attachments.stored_path', '!=', '')
            ->count();

        $tariff = $user->tariff();

        return new UserMediaQuotaSnapshot(
            tariff: $tariff,
            storageUsedBytes: $storageUsedBytes,
            storageLimitBytes: $tariff->getMaxStorageBytes(),
            filesUsedCount: $filesUsedCount,
            filesLimitCount: $tariff->getMaxMediaFilesCount(),
        );
    }
}
