<?php

declare(strict_types=1);

namespace App\Services\Quota\Cleanup\Contracts;

use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Builder;

/**
 * Задаёт порядок удаления медиа при принудительном приведении к квоте.
 */
interface MediaCleanupOrderingStrategyInterface
{
    /**
     * Возвращает уникальный код стратегии.
     *
     * @return string
     */
    public function code(): string;

    /**
     * Сортирует запрос вложений: первыми будут удаляться записи в этом порядке.
     *
     * @param Builder<MediaAttachment> $query базовый запрос вложений одного пользователя
     *
     * @return Builder<MediaAttachment>
     */
    public function apply(Builder $query): Builder;
}
