<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Import\Preparations\MessagePreparer;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class AbstractParser implements ParserInterface
{
    /**
     * The class-string of the Message model corresponding to the parser.
     * Assuming that each messenger parser corresponds to its own Message model for that messenger from the
     * \App\Models namespace.
     *
     * @see Message
     */
    public const PARSER_CORRESPONDING_MESSAGE_MODEL = Message::class;

    /**
     * @param MessagePreparer $messagePreparer
     */
    public function __construct(
        protected readonly MessagePreparer $messagePreparer
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getMessageModelClass(): string
    {
        return static::PARSER_CORRESPONDING_MESSAGE_MODEL;
    }

    /**
     * @inheritDoc
     */
    public function getMessagesRelation(Conversation $conversation): HasMany
    {
        return $conversation->messages(static::PARSER_CORRESPONDING_MESSAGE_MODEL);
    }

    /**
     * @inheritDoc
     */
    abstract public function parse(string $path): ConversationImportDTO;

    /**
     * Экранирует текст сообщения для записи в БД.
     *
     * @param array<string, mixed> $message
     *
     * @return array<string, mixed>
     */
    protected function prepareMessageText(array $message): array
    {
        $text = $message['text'] ?? null;
        if (is_string($text)) {
            $message['text'] = $this->messagePreparer->prepare($text);
        }

        return $message;
    }
}
