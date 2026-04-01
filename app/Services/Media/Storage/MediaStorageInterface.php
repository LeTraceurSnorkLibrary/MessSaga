<?php

declare(strict_types=1);

namespace App\Services\Media\Storage;

interface MediaStorageInterface
{
    /**
     * @param string $path
     * @param mixed  $contents
     *
     * @return bool
     */
    public function putStream(string $path, mixed $contents): bool;

    /**
     * @param string $path
     *
     * @return mixed resource|false
     */
    public function readStream(string $path): mixed;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function delete(string $path): bool;

    /**
     * @param string $path
     *
     * @return string|null
     */
    public function mimeType(string $path): ?string;
}
