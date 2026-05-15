<?php

declare(strict_types=1);

namespace App\Services\Quota;

use App\Models\MediaAttachment;
use App\Models\User;
use App\Services\Media\Storage\MediaStorageInterface;
use App\Services\Quota\Cleanup\Contracts\MediaCleanupOrderingStrategyInterface;
use App\Services\Quota\Cleanup\MediaCleanupOrderingStrategyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpiredMediaQuotaEnforcerService
{
    /**
     * @var string
     */
    private string $defaultCleanupStrategyCode = 'newest';

    /**
     * @param UserMediaQuotaService               $userMediaQuotaService
     * @param MediaStorageInterface               $mediaStorage
     * @param MediaCleanupOrderingStrategyFactory $cleanupOrderingStrategyFactory
     */
    public function __construct(
        private readonly UserMediaQuotaService               $userMediaQuotaService,
        private readonly MediaStorageInterface               $mediaStorage,
        private readonly MediaCleanupOrderingStrategyFactory $cleanupOrderingStrategyFactory
    ) {
    }

    /**
     * @param User                                       $user
     * @param MediaCleanupOrderingStrategyInterface|null $strategyOverride
     *
     * @return int
     */
    public function enforceForUser(
        User                                   $user,
        ?MediaCleanupOrderingStrategyInterface $strategyOverride = null
    ): int {
        $graceUntil = $user->media_quota_grace_until;
        if (!$graceUntil instanceof Carbon || $graceUntil->isFuture()) {
            return 0;
        }

        $snapshot = $this->userMediaQuotaService->snapshot($user);
        if ($snapshot->canUploadMedia()) {
            $user->media_quota_grace_until = null;
            $user->save();

            return 0;
        }

        $strategy = $strategyOverride
            ?? $this->cleanupOrderingStrategyFactory->make(
                $user->media_cleanup_strategy
                    ?: $this->defaultCleanupStrategyCode
            );

        $remainingStorageBytes = $snapshot->getStorageUsedBytes();
        $remainingFilesCount   = $snapshot->getFilesUsedCount();
        $storageLimitBytes     = $snapshot->getStorageLimitBytes();
        $filesLimitCount       = $snapshot->getFilesLimitCount();

        $deletedCount = 0;
        $attachments  = $this->attachmentsForCleanup($user, $strategy)->get();
        foreach ($attachments as $attachment) {
            if ($remainingStorageBytes <= $storageLimitBytes && $remainingFilesCount <= $filesLimitCount) {
                break;
            }

            $storedPath = trim((string)($attachment->stored_path ?? ''));
            if ($storedPath === '') {
                continue;
            }

            if (!$this->mediaStorage->delete($storedPath)) {
                Log::warning('Failed to delete media from storage during quota enforcement', [
                    'user_id'             => $user->id,
                    'media_attachment_id' => $attachment->id,
                    'stored_path'         => $storedPath,
                ]);

                continue;
            }

            $attachmentSize = max(0, (int)$attachment->size_bytes);
            DB::transaction(function () use ($attachment): void {
                $attachment->stored_path       = null;
                $attachment->media_type        = null;
                $attachment->mime_type         = null;
                $attachment->original_filename = null;
                $attachment->size_bytes        = 0;
                $attachment->save();
            });

            $remainingStorageBytes = max(0, $remainingStorageBytes - $attachmentSize);
            $remainingFilesCount   = max(0, $remainingFilesCount - 1);
            $deletedCount++;
        }

        if ($remainingStorageBytes <= $storageLimitBytes && $remainingFilesCount <= $filesLimitCount) {
            $user->media_quota_grace_until = null;
            $user->save();
        }

        return $deletedCount;
    }

    /**
     * @return Builder<MediaAttachment>
     */
    private function attachmentsForCleanup(
        User                                  $user,
        MediaCleanupOrderingStrategyInterface $strategy
    ): Builder {
        $query = MediaAttachment::query()
            ->select('media_attachments.*')
            ->join('conversations', 'conversations.id', '=', 'media_attachments.conversation_id')
            ->join('messenger_accounts', 'messenger_accounts.id', '=', 'conversations.messenger_account_id')
            ->where('messenger_accounts.user_id', $user->id)
            ->whereNotNull('media_attachments.stored_path')
            ->where('media_attachments.stored_path', '!=', '');

        return $strategy->apply($query);
    }
}
