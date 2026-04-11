<?php

declare(strict_types=1);

namespace App\Support\Admin\Statistics;

use App\Models\TelegramMessage;
use App\Models\User;
use App\Models\ViberMessage;
use App\Models\WhatsAppMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Считает число импортированных сообщений на пользователя по всем каналам.
 *
 * Новый мессенджер: добавьте модель сообщения в {@see messageModelClasses()}.
 */
final class UserMessageCountAggregates
{
    /**
     * Список моделей,
     *
     * @return list<class-string<Model>>
     */
    public static function messageModelClasses(): array
    {
        return [
            TelegramMessage::class,
            WhatsAppMessage::class,
            ViberMessage::class,
        ];
    }

    /**
     * Добавляет к запросу по `users` подзапросы по каждому каналу и поле `messages_count`.
     *
     * @param Builder<User> $usersQuery
     *
     * @return Builder<User>
     */
    public static function withTotalMessagesCount(Builder $usersQuery): Builder
    {
        $usersQuery->select('users.*');

        $sumParts = [];
        foreach (array_values(self::messageModelClasses()) as $index => $modelClass) {
            /**
             * @var Model $model
             */
            $model     = new $modelClass();
            $table     = $model->getTable();
            $joinAlias = 'imported_msg_' . $index;

            $sub = $modelClass::query()
                ->selectRaw('messenger_accounts.user_id AS user_id')
                ->selectRaw('COUNT(*) AS cnt')
                ->join('conversations', 'conversations.id', '=', $table . '.conversation_id')
                ->join(
                    'messenger_accounts',
                    'messenger_accounts.id',
                    '=',
                    'conversations.messenger_account_id'
                )
                ->groupBy('messenger_accounts.user_id');

            $usersQuery->leftJoinSub($sub, $joinAlias, function ($join) use ($joinAlias): void {
                $join->on($joinAlias . '.user_id', '=', 'users.id');
            });

            $sumParts[] = 'COALESCE(' . $joinAlias . '.cnt, 0)';
        }

        if ($sumParts === []) {
            return $usersQuery->selectRaw('0 AS messages_count');
        }

        return $usersQuery->selectRaw('(' . implode(' + ', $sumParts) . ') AS messages_count');
    }
}
