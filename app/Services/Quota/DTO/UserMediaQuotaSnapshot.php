<?php

declare(strict_types=1);

namespace App\Services\Quota\DTO;

use App\Tariffs\Contracts\TariffInterface;

final readonly class UserMediaQuotaSnapshot
{
    /**
     * @param TariffInterface $tariff
     * @param int             $storageUsedBytes
     * @param int             $storageLimitBytes
     * @param int             $filesUsedCount
     * @param int             $filesLimitCount
     */
    public function __construct(
        private TariffInterface $tariff,
        private int             $storageUsedBytes,
        private int             $storageLimitBytes,
        private int             $filesUsedCount,
        private int             $filesLimitCount,
    ) {
    }

    /**
     * @return int
     */
    public function getStorageUsedBytes(): int
    {
        return $this->storageUsedBytes;
    }

    /**
     * @return int
     */
    public function getStorageLimitBytes(): int
    {
        return $this->storageLimitBytes;
    }

    /**
     * @return int
     */
    public function getFilesUsedCount(): int
    {
        return $this->filesUsedCount;
    }

    /**
     * @return int
     */
    public function getFilesLimitCount(): int
    {
        return $this->filesLimitCount;
    }

    /**
     * @return int
     */
    public function getRemainingStorageBytes(): int
    {
        return max(0, $this->getStorageLimitBytes() - $this->getStorageUsedBytes());
    }

    /**
     * @return int
     */
    public function getRemainingFilesCount(): int
    {
        return max(0, $this->getFilesLimitCount() - $this->getFilesUsedCount());
    }

    /**
     * @return bool
     */
    public function canUploadMedia(): bool
    {
        return $this->getMediaUploadBlockReason() === null;
    }

    /**
     * @return string|null
     */
    public function getMediaUploadBlockReason(): ?string
    {
        if ($this->getRemainingFilesCount() <= 0) {
            return 'quota_files_exceeded';
        }

        if ($this->getRemainingStorageBytes() <= 0) {
            return 'quota_storage_exceeded';
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tariff'  => [
                'code'  => $this->tariff->getName(),
                'label' => $this->tariff->getLabel(),
            ],
            'storage' => [
                'used'      => $this->getStorageUsedBytes(),
                'limit'     => $this->getStorageLimitBytes(),
                'remaining' => $this->getRemainingStorageBytes(),
            ],
            'files'   => [
                'used'      => $this->getFilesUsedCount(),
                'limit'     => $this->getFilesLimitCount(),
                'remaining' => $this->getRemainingFilesCount(),
            ],
        ];
    }
}

