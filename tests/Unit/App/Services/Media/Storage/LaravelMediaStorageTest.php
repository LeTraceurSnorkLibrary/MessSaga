<?php

declare(strict_types=1);

namespace Tests\Unit\App\Services\Media\Storage;

use App\Services\Media\Storage\LaravelMediaStorage;
use Illuminate\Filesystem\FilesystemAdapter;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Exception;
use Tests\TestCase;

#[CoversMethod(LaravelMediaStorage::class, '__construct')]
#[CoversMethod(LaravelMediaStorage::class, 'putStream')]
#[CoversMethod(LaravelMediaStorage::class, 'readStream')]
#[CoversMethod(LaravelMediaStorage::class, 'exists')]
#[CoversMethod(LaravelMediaStorage::class, 'delete')]
#[CoversMethod(LaravelMediaStorage::class, 'mimeType')]
final class LaravelMediaStorageTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function test_put_stream_returns_true_when_disk_put_returns_true(): void
    {
        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())
            ->method('put')
            ->with('foo/bar.txt', 'payload')
            ->willReturn(true);

        $storage = new LaravelMediaStorage($disk);

        $this->assertTrue($storage->putStream('foo/bar.txt', 'payload'));
    }

    /**
     * @throws Exception
     */
    public function test_put_stream_returns_false_when_disk_put_returns_false(): void
    {
        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())
            ->method('put')
            ->with('foo/bar.txt', 'payload')
            ->willReturn(false);

        $storage = new LaravelMediaStorage($disk);

        $this->assertFalse($storage->putStream('foo/bar.txt', 'payload'));
    }

    /**
     * @throws Exception
     */
    public function test_read_stream_delegates_to_disk(): void
    {
        $stream = fopen('php://temp', 'rb');
        $this->assertIsResource($stream);

        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())
            ->method('readStream')
            ->with('foo/bar.txt')
            ->willReturn($stream);

        $storage = new LaravelMediaStorage($disk);

        $this->assertSame($stream, $storage->readStream('foo/bar.txt'));
        fclose($stream);
    }

    /**
     * @throws Exception
     */
    public function test_exists_delegates_to_disk(): void
    {
        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())
            ->method('exists')
            ->with('foo/bar.txt')
            ->willReturn(true);

        $storage = new LaravelMediaStorage($disk);

        $this->assertTrue($storage->exists('foo/bar.txt'));
    }

    /**
     * @throws Exception
     */
    public function test_delete_delegates_to_disk(): void
    {
        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())
            ->method('delete')
            ->with('foo/bar.txt')
            ->willReturn(true);

        $storage = new LaravelMediaStorage($disk);

        $this->assertTrue($storage->delete('foo/bar.txt'));
    }

    /**
     * @throws Exception
     */
    public function test_mime_type_returns_null_when_disk_returns_empty_string(): void
    {
        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())
            ->method('mimeType')
            ->with('a')
            ->willReturn('');

        $storage = new LaravelMediaStorage($disk);

        $this->assertNull($storage->mimeType('a'));
    }

    public function test_mime_type_returns_null_when_disk_returns_non_string(): void
    {
        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())
            ->method('mimeType')
            ->with('b')
            ->willReturn(false);

        $storage = new LaravelMediaStorage($disk);

        $this->assertNull($storage->mimeType('b'));
    }

    public function test_mime_type_returns_value_when_disk_returns_non_empty_string(): void
    {
        $disk = $this->createMock(FilesystemAdapter::class);
        $disk->expects($this->once())
            ->method('mimeType')
            ->with('foo/bar.jpg')
            ->willReturn('image/jpeg');

        $storage = new LaravelMediaStorage($disk);

        $this->assertSame('image/jpeg', $storage->mimeType('foo/bar.jpg'));
    }
}
