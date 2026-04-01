<?php

declare(strict_types=1);

namespace Tests\Unit\App\Models\MediaAttachment;

use App\Models\Conversation;
use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\Attributes\CoversMethod;
use Tests\TestCase;

#[CoversMethod(MediaAttachment::class, 'conversation')]
final class ConversationTest extends TestCase
{
    public function test_conversation_is_belongs_to_conversation_model(): void
    {
        $attachment = new MediaAttachment();
        $relation   = $attachment->conversation();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Conversation::class, $relation->getRelated());
    }
}
