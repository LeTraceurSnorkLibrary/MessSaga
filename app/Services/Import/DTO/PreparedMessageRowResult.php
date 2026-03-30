<?php

declare(strict_types=1);

namespace App\Services\Import\DTO;

final readonly class PreparedMessageRowResult
{
    /**
     * @param array<string, mixed>      $row
     * @param array<string, mixed>|null $media
     */
    public function __construct(
        private array  $row,
        private ?array $media = null
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getRow(): array
    {
        return $this->row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getMedia(): ?array
    {
        return $this->media;
    }
}
