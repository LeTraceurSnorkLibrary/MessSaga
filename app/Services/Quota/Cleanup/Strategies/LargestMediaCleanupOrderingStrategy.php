<?php

declare(strict_types=1);

namespace App\Services\Quota\Cleanup\Strategies;

use App\Models\MediaAttachment;
use App\Services\Quota\Cleanup\Contracts\MediaCleanupOrderingStrategyInterface;
use Illuminate\Database\Eloquent\Builder;

final class LargestMediaCleanupOrderingStrategy implements MediaCleanupOrderingStrategyInterface
{
    public function code(): string
    {
        return 'largest';
    }

    /**
     * @param Builder<MediaAttachment> $query
     *
     * @return Builder<MediaAttachment>
     */
    public function apply(Builder $query): Builder
    {
        return $query
            ->orderByDesc('media_attachments.size_bytes')
            ->orderByDesc('media_attachments.id');
    }
}
