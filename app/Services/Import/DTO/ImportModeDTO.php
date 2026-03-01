<?php

declare(strict_types=1);

namespace App\Services\Import\DTO;

/**
 * DTO с параметрами режима импорта
 */
final readonly class ImportModeDTO
{
    /**
     * @param string   $mode                 Режим импорта (auto, new, select)
     * @param int      $userId               ID пользователя, который вызывает запрос
     * @param int|null $targetConversationId ID переписки для режима select
     */
    public function __construct(
        private string $mode,
        private int    $userId,
        private ?int   $targetConversationId = null,
    ) {
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return int|null
     */
    public function getTargetConversationId(): ?int
    {
        return $this->targetConversationId;
    }
}
