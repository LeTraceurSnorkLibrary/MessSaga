<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Результат разбора файла экспорта мессенджера.
 * Парсер заполняет данные переписки и массив сообщений в формате, готовом для модели сообщений
 * (ключи совпадают с fillable соответствующей модели; conversation_id и timestamps добавляет ImportService).
 */
final readonly class ConversationImportDTO
{
    /**
     * @param array<array-key, mixed>                $conversationData  Данные для создания/обновления аккаунта и
     *                                                                  переписки: external_id, title, participants,
     *                                                                  account_name, account_meta
     * @param array<array-key, array<string, mixed>> $messages          Массив сообщений: каждый элемент — массив с
     *                                                                  ключами под fillable модели сообщений
     *                                                                  (text — ещё не зашифрован)
     */
    public function __construct(
        private array $conversationData,
        private array $messages
    ) {
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getConversationData(): array
    {
        return $this->conversationData;
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @return bool
     */
    public function hasConversation(): bool
    {
        return !empty($this->conversationData);
    }
}
