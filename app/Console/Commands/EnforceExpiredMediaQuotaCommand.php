<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Quota\Cleanup\MediaCleanupOrderingStrategyFactory;
use App\Services\Quota\ExpiredMediaQuotaEnforcerService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class EnforceExpiredMediaQuotaCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'quota:enforce-media
        {--user-id= : Enforce only for a specific user id}
        {--strategy= : Override cleanup strategy (newest|oldest|largest|smallest)}';

    /**
     * @var string
     */
    protected $description = 'Delete media files for users whose quota grace period is expired';

    /**
     * @param ExpiredMediaQuotaEnforcerService    $enforcer
     * @param MediaCleanupOrderingStrategyFactory $strategyFactory
     *
     * @return int
     */
    public function handle(
        ExpiredMediaQuotaEnforcerService    $enforcer,
        MediaCleanupOrderingStrategyFactory $strategyFactory
    ): int {
        $strategy     = $strategyFactory->make((string)$this->option('strategy'));
        $singleUserId = $this->option('user-id');

        $query = User::query()
            ->whereNotNull('media_quota_grace_until')
            ->where('media_quota_grace_until', '<=', Carbon::now());

        if (is_numeric($singleUserId)) {
            $query->whereKey((int)$singleUserId);
        }

        $totalDeleted  = 0;
        $affectedUsers = 0;

        $query->eachById(function (User $user) use ($enforcer, $strategy, &$totalDeleted, &$affectedUsers): void {
            $deletedForUser = $enforcer->enforceForUser($user, $strategy);
            if ($deletedForUser > 0) {
                $affectedUsers++;
                $totalDeleted += $deletedForUser;
            }
        });

        $this->info(sprintf(
            'Quota enforcement complete: affected_users=%d, deleted_media=%d',
            $affectedUsers,
            $totalDeleted
        ));

        return self::SUCCESS;
    }
}
