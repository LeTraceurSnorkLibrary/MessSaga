<?php

declare(strict_types=1);

namespace App\Services\Quota\Cleanup\Strategies;

use App\Models\MediaAttachment;
use App\Services\Quota\Cleanup\Contracts\MediaCleanupOrderingStrategyInterface;
use Illuminate\Database\Eloquent\Builder;

final class OldestMediaCleanupOrderingStrategy implements MediaCleanupOrderingStrategyInterface
{
    public function code(): string
    {
        return 'oldest';
    }

    /**
     * @param Builder<MediaAttachment> $query
     *
     * @return Builder<MediaAttachment>
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->orderBy('media_attachments.created_at')
            ->orderBy('media_attachments.id');
    }
}
