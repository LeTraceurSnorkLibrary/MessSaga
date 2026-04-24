<?php

declare(strict_types=1);

namespace App\Services\Quota\Cleanup\Contracts;

use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Builder;

interface MediaCleanupOrderingStrategyInterface
{
    /**
     * @return string
     */
    public function code(): string;

    /**
     * @param Builder<MediaAttachment> $query
     *
     * @return Builder<MediaAttachment>
     */
    public function apply(Builder $query): Builder;
}
