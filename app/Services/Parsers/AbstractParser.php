<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\Conversation;
use App\Models\Message;
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
}
