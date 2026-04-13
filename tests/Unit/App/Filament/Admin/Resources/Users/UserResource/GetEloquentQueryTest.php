<?php

declare(strict_types=1);

namespace Tests\Unit\App\Filament\Admin\Resources\Users\UserResource;

use App\Filament\Admin\Resources\Users\UserResource;
use App\Support\Admin\Statistics\UserMessageCountAggregates;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(UserResource::class, 'getEloquentQuery')]
#[CoversMethod(UserMessageCountAggregates::class, 'withTotalMessagesCount')]
#[CoversMethod(UserMessageCountAggregates::class, 'messageModelClasses')]
final class GetEloquentQueryTest extends TestCase
{
    public function test_returns_query_with_messages_count_projection(): void
    {
        $query = UserResource::getEloquentQuery();
        $sql   = $query->toSql();

        $this->assertStringContainsString('from "users"', $sql);
        $this->assertStringContainsString('AS messages_count', $sql);
    }
}
