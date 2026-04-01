<?php

declare(strict_types=1);

namespace App\Services\Media\Storage;

use Illuminate\Filesystem\FilesystemAdapter;

final readonly class LaravelMediaStorage implements MediaStorageInterface
{
    /**
     * @param FilesystemAdapter $disk
     */
    public function __construct(
        private FilesystemAdapter $disk
    ) {
    }

    /**
     * @inheritdoc
     */
    public function putStream(string $path, mixed $contents): bool
    {
        return $this->disk->put($path, $contents) === true;
    }

    /**
     * @inheritdoc
     */
    public function readStream(string $path): mixed
    {
        return $this->disk->readStream($path);
    }

    /**
     * @inheritdoc
     */
    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $path): bool
    {
        return $this->disk->delete($path);
    }

    /**
     * @inheritdoc
     */
    public function mimeType(string $path): ?string
    {
        $mime = $this->disk->mimeType($path);

        return is_string($mime) && $mime !== ''
            ? $mime
            : null;
    }
}
