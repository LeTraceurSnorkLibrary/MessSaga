<?php

declare(strict_types=1);

namespace Tests\Unit\App\DTO\ConversationImportDTO;

use App\DTO\ConversationImportDTO;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ConversationImportDTO::class, '__construct')]
#[CoversMethod(ConversationImportDTO::class, 'getConversationData')]
#[CoversMethod(ConversationImportDTO::class, 'getMessages')]
#[CoversMethod(ConversationImportDTO::class, 'hasConversation')]
class ConstructorGettersTest extends TestCase
{
    public function test_it_returns_conversation_data_and_messages_from_constructor(): void
    {
        $conversationData = [
            'external_id'  => 'chat-42',
            'title'        => 'Team Chat',
            'participants' => ['alice', 'bob'],
            'account_name' => 'Alice',
            'account_meta' => ['source' => 'telegram'],
        ];
        $messages         = [
            [
                'external_id' => 'msg-1',
                'text'        => 'Hello',
                'sender_name' => 'alice',
            ],
            [
                'external_id' => 'msg-2',
                'text'        => 'Hi',
                'sender_name' => 'bob',
            ],
        ];

        $dto = new ConversationImportDTO($conversationData, $messages);

        $this->assertSame($conversationData, $dto->getConversationData());
        $this->assertSame($messages, $dto->getMessages());
    }

    public function test_has_conversation_returns_true_for_non_empty_conversation_data(): void
    {
        $dto = new ConversationImportDTO(['title' => 'Any chat'], []);

        $this->assertTrue($dto->hasConversation());
    }

    public function test_has_conversation_returns_false_for_empty_conversation_data(): void
    {
        $dto = new ConversationImportDTO([], []);

        $this->assertFalse($dto->hasConversation());
    }
}
