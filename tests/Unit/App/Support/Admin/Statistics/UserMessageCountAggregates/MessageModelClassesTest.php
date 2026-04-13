<?php

declare(strict_types=1);

namespace Tests\Unit\App\Support\Admin\Statistics\UserMessageCountAggregates;

use App\Models\TelegramMessage;
use App\Models\ViberMessage;
use App\Models\WhatsAppMessage;
use App\Support\Admin\Statistics\UserMessageCountAggregates;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(UserMessageCountAggregates::class, 'messageModelClasses')]
final class MessageModelClassesTest extends TestCase
{
    public function test_returns_all_message_model_classes(): void
    {
        $this->assertSame(
            [
                TelegramMessage::class,
                WhatsAppMessage::class,
                ViberMessage::class,
            ],
            UserMessageCountAggregates::messageModelClasses()
        );
    }
}
