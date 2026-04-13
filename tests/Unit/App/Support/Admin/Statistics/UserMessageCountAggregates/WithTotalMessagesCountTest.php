<?php

declare(strict_types=1);

namespace Tests\Unit\App\Support\Admin\Statistics\UserMessageCountAggregates;

use App\Models\User;
use App\Support\Admin\Statistics\UserMessageCountAggregates;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(UserMessageCountAggregates::class, 'withTotalMessagesCount')]
#[CoversMethod(UserMessageCountAggregates::class, 'messageModelClasses')]
final class WithTotalMessagesCountTest extends TestCase
{
    public function test_enriches_users_query_with_messages_count_aggregation(): void
    {
        $query = UserMessageCountAggregates::withTotalMessagesCount(User::query());
        $sql   = $query->toSql();

        $this->assertStringContainsString('from "users"', $sql);
        $this->assertStringContainsString('left join', $sql);
        $this->assertStringContainsString('messenger_accounts.user_id AS user_id', $sql);
        $this->assertStringContainsString('COUNT(*) AS cnt', $sql);
        $this->assertStringContainsString('AS messages_count', $sql);
    }
}
