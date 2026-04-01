<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Import\DTO\PreparedMessageRowResult;

use App\Services\Import\DTO\PreparedMessageRowResult;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(PreparedMessageRowResult::class, '__construct')]
#[CoversMethod(PreparedMessageRowResult::class, 'getRow')]
#[CoversMethod(PreparedMessageRowResult::class, 'getMedia')]
final class ConstructorGettersTest extends TestCase
{
    public function test_supports_null_media_payload(): void
    {
        $row = ['text' => 'hello', 'conversation_id' => 77];
        $dto = new PreparedMessageRowResult($row);

        $this->assertSame($row, $dto->getRow());
        $this->assertNull($dto->getMedia());
    }

    public function test_returns_media_payload_when_present(): void
    {
        $media = [
            'stored_path' => 'conversations/1/media/file.jpg',
            'mime_type'   => 'image/jpeg',
        ];
        $dto = new PreparedMessageRowResult(['id' => 1], $media);

        $this->assertSame($media, $dto->getMedia());
    }
}
