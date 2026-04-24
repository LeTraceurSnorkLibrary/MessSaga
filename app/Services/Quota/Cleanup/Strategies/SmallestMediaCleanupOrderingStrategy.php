<?php

declare(strict_types=1);

namespace App\Services\Quota\Cleanup\Strategies;

use App\Models\MediaAttachment;
use App\Services\Quota\Cleanup\Contracts\MediaCleanupOrderingStrategyInterface;
use Illuminate\Database\Eloquent\Builder;

final class SmallestMediaCleanupOrderingStrategy implements MediaCleanupOrderingStrategyInterface
{
    public function code(): string
    {
        return 'smallest';
    }

    /**
     * @param Builder<MediaAttachment> $query
     *
     * @return Builder<MediaAttachment>
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->orderBy('media_attachments.size_bytes')
            ->orderBy('media_attachments.id');
    }
}
