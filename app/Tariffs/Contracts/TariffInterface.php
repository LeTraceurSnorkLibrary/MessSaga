<?php

declare(strict_types=1);

namespace App\Tariffs\Contracts;

interface TariffInterface
{
    /**
     * Tariff's stringed ID
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Tariff's label (human-readable)
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * True if tariff allows media files upload
     *
     * @return bool
     */
    public function allowsMediaUpload(): bool;

    /**
     * Uploaded media files limit in bytes
     *
     * @return int
     */
    public function getMaxStorageBytes(): int;

    /**
     * Uploaded media files count limit
     *
     * @return int
     */
    public function getMaxMediaFilesCount(): int;
}

