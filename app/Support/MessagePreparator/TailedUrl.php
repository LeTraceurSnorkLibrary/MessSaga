<?php

declare(strict_types=1);

namespace App\Support\MessagePreparator;

/**
 * DTO для хранения сущности "Ссылка с 'хвостом'"
 */
final readonly class TailedUrl
{
    /**
     * @param string $url  Основная часть ссылки
     * @param string $tail "Хвост" ссылки (знаки пунктуации и пр., что не относится к основному URL, но не отделено от
     *                     него пробелом)
     */
    public function __construct(
        private string $url,
        private string $tail
    ) {
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getTail(): string
    {
        return $this->tail;
    }
}
