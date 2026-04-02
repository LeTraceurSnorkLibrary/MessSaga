<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\MediaAttachment;
use App\Models\MessengerAccount;
use App\Models\User;
use App\Services\Media\Storage\MediaStorageInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

class ConversationDestroyDeletesMediaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    public function test_destroy_conversation_removes_linked_media_files_from_storage(): void
    {
        $user         = User::factory()->create();
        $account      = MessengerAccount::query()->create([
            'user_id' => $user->id,
            'type'    => 'telegram',
            'name'    => 'Account',
            'meta'    => [],
        ]);
        $conversation = Conversation::query()->create([
            'messenger_account_id' => $account->id,
            'external_id'          => 'c1',
            'title'                => 'Chat',
            'participants'         => [],
        ]);
        $attachment   = MediaAttachment::query()->create([
            'conversation_id'   => $conversation->id,
            'stored_path'       => 'conversations/1/media/file.jpg',
            'export_path'       => 'file.jpg',
            'media_type'        => 'image',
            'mime_type'         => 'image/jpeg',
            'original_filename' => 'file.jpg',
        ]);

        $mediaStorage = $this->createMock(MediaStorageInterface::class);
        $mediaStorage->expects($this->once())
            ->method('exists')
            ->with('conversations/1/media/file.jpg')
            ->willReturn(true);
        $mediaStorage->expects($this->once())
            ->method('delete')
            ->with('conversations/1/media/file.jpg')
            ->willReturn(true);
        $this->app->instance(MediaStorageInterface::class, $mediaStorage);

        $response = $this->actingAs($user)
            ->delete(route('api.conversations.destroy', ['conversation' => $conversation->id]));

        $response->assertNoContent();
        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('media_attachments', ['id' => $attachment->id]);
    }
}
