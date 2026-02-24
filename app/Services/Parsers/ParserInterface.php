<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\Message;

interface ParserInterface
{
    /**
     * Разбирает файл экспорта и возвращает DTO с данными переписки и сообщений.
     * Ключи в каждом сообщении должны соответствовать fillable модели, возвращаемой getMessageModelClass().
     *
     * @param string $path
     *
     * @return ConversationImportDTO
     */
    public function parse(string $path): ConversationImportDTO;

    /**
     * Класс модели сообщений для этого мессенджера (TelegramMessage::class, WhatsAppMessage::class и т.д.).
     *
     * @return class-string<Message>
     */
    public function getMessageModelClass(): string;
}
