<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\MessagePreparationService;

use App\Models\MediaTypes\SupportedMediaTypesEnum;
use App\Models\Message;
use App\Services\Import\MessagePreparationService;
use App\Services\Media\ImportedMediaResolverService;
use App\Services\Media\Storage\MediaStorageInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

#[CoversMethod(MessagePreparationService::class, '__construct')]
#[CoversMethod(MessagePreparationService::class, 'prepareMessageRowForInsert')]
#[CoversMethod(MessagePreparationService::class, 'normalizeExportPath')]
final class PrepareMessageRowForInsertTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_prepares_row_filters_fillable_and_encrypts_text(): void
    {
        $service = new MessagePreparationService(
            $this->createStub(ImportedMediaResolverService::class),
            $this->createStub(MediaStorageInterface::class)
        );
        $message = [
            'external_id'            => 'ext-1',
            'sender_name'            => 'alice',
            'sender_external_id'     => '42',
            'sent_at'                => Carbon::parse('2026-03-04 12:13:14'),
            'text'                   => 'secret',
            'dedup_hash'             => 'hash',
            'attachment_export_path' => null,
            'attachment_stored_path' => 'must-be-removed',
            'unknown_field'          => 'drop',
        ];

        $result = $service->prepareMessageRowForInsert($message, 99, TestPreparationMessage::class);
        $row    = $result->getRow();

        $this->assertSame(99, $row['conversation_id']);
        $this->assertSame('ext-1', $row['external_id']);
        $this->assertSame('alice', $row['sender_name']);
        $this->assertSame('42', $row['sender_external_id']);
        $this->assertSame('2026-03-04 12:13:14', $row['sent_at']);
        $this->assertSame('hash', $row['dedup_hash']);
        $this->assertArrayNotHasKey('unknown_field', $row);
        $this->assertArrayNotHasKey('attachment_export_path', $row);
        $this->assertArrayNotHasKey('attachment_stored_path', $row);
        $this->assertSame('secret', Crypt::decryptString((string)$row['text']));
        $this->assertNull($result->getMedia());
    }

    /**
     * @throws Exception
     */
    public function test_builds_media_payload_with_normalized_export_path_and_mime_type(): void
    {
        $mediaStorage = $this->createMock(MediaStorageInterface::class);
        $mediaStorage->expects($this->once())->method('exists')
            ->with('conversations/1/media/file.mp3')
            ->willReturn(true);
        $mediaStorage->expects($this->once())->method('mimeType')
            ->with('conversations/1/media/file.mp3')
            ->willReturn('audio/mpeg');

        $service = new MessagePreparationService(
            $this->createStub(ImportedMediaResolverService::class),
            $mediaStorage
        );
        $result  = $service->prepareMessageRowForInsert(
            [
                'external_id'            => 'x',
                'sent_at'                => '2026-02-01 01:02:03',
                'text'                   => 'hello',
                'attachment_export_path' => ' folder\\clip.mp3 ',
            ],
            1,
            TestPreparationMessage::class,
            'conversations/1/media/file.mp3'
        );

        $media = $result->getMedia();

        $this->assertNotNull($media);
        $this->assertSame('conversations/1/media/file.mp3', $media['stored_path']);
        $this->assertSame('folder/clip.mp3', $media['export_path']);
        $this->assertSame(SupportedMediaTypesEnum::AUDIO->value, $media['media_type']);
        $this->assertSame('audio/mpeg', $media['mime_type']);
        $this->assertSame('clip.mp3', $media['original_filename']);
    }
}

final class TestPreparationMessage extends Message
{
    protected $table = 'test_preparation_messages';

    protected $fillable = [
        'conversation_id',
        'external_id',
        'sender_name',
        'sender_external_id',
        'sent_at',
        'text',
        'dedup_hash',
    ];
}
