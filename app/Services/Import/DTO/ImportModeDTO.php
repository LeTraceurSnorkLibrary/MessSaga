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
     * @param int|null $targetConversationId ID переписки для режима select
     */
    public function __construct(
        public string $mode,
        public ?int   $targetConversationId = null,
    ) {
    }

    /**
     * Создаёт DTO из запроса
     */
    public static function fromRequest(string $mode, ?int $targetConversationId = null): self
    {
        return new self($mode, $targetConversationId);
    }

    /**
     * Проверяет, является ли режим select
     */
    public function isSelectMode(): bool
    {
        return $this->mode === ImportModeEnum::SELECT->value;
    }

    /**
     * Проверяет, является ли режим new
     */
    public function isNewMode(): bool
    {
        return $this->mode === ImportModeEnum::NEW->value;
    }

    /**
     * Проверяет, является ли режим auto
     */
    public function isAutoMode(): bool
    {
        return $this->mode === ImportModeEnum::AUTO->value;
    }

    /**
     * Возвращает название режима
     */
    public function getName(): string
    {
        return $this->mode;
    }
}
