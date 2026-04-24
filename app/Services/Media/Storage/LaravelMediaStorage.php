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
        $normalized = ltrim($path, '/');

        return $this->disk->exists($path) || ($normalized !== $path && $this->disk->exists($normalized));
    }

    /**
     * @inheritdoc
     */
    public function delete(string $path): bool
    {
        $normalized = ltrim($path, '/');

        if ($this->disk->delete($path)) {
            return true;
        }

        return $normalized !== $path && $this->disk->delete($normalized);
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
