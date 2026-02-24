<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface ParserInterface
{
    /**
     * Разбирает файл экспорта и возвращает DTO с данными переписки и сообщений.
     *
     * @param string $path
     *
     * @return ConversationImportDTO
     */
    public function parse(string $path): ConversationImportDTO;

    /**
     * Класс модели сообщений для этого мессенджера.
     *
     * @return class-string<Message>
     */
    public function getMessageModelClass(): string;

    /**
     * Получить relation для сообщений конкретной переписки.
     * Используется в контроллерах для получения сообщений.
     *
     * @param Conversation $conversation
     *
     * @return HasMany
     */
    public function getMessagesRelation(Conversation $conversation): HasMany;
}
