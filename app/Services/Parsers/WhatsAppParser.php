<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\WhatsAppMessage;
use App\Services\Parsers\WhatsApp\ContentParser;
use App\Services\Parsers\WhatsApp\LineTypeEnum;
use App\Services\Parsers\WhatsApp\MessageBuilder;
use InvalidArgumentException;
use RuntimeException;

class WhatsAppParser extends AbstractParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public const PARSER_CORRESPONDING_MESSAGE_MODEL = WhatsAppMessage::class;

    /**
     * @param ContentParser  $contentParser
     * @param MessageBuilder $messageBuilder
     */
    public function __construct(
        private readonly ContentParser $contentParser,
        private readonly MessageBuilder $messageBuilder,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function parse(string $path): ConversationImportDTO
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return new ConversationImportDTO([], []);
        }

        $lines = explode("\n", $content);
        if (empty($lines)) {
            return new ConversationImportDTO([], []);
        }

        $participants     = $this->contentParser->parseParticipants($lines);
        $conversationData = $this->contentParser->parseConversationData($lines, $participants);
        $messages         = $this->parseMessages($lines);

        return new ConversationImportDTO($conversationData, $messages);
    }

    /**
     * Parse lines into messages
     *
     * @param string[] $lines
     *
     * @return array<array>
     */
    private function parseMessages(array $lines): array
    {
        $groups = $this->groupLinesByType($lines);

        return array_map(
            fn (array $group) => $this->processGroup($group),
            $groups
        );
    }

    /**
     * Group lines into messages
     *
     * @param string[] $lines
     *
     * @return array<int, array{type: LineTypeEnum, lines: string[]}>
     */
    private function groupLinesByType(array $lines): array
    {
        $groups       = [];
        $currentGroup = null;

        foreach ($lines as $line) {
            $trimmedLine = rtrim($line, "\r");
            $type        = $this->contentParser->detectLineType($trimmedLine);

            // Начинаем новую группу, если строка начинает сообщение
            if ($type->isNewMessage()) {
                if ($currentGroup !== null) {
                    $groups[] = $currentGroup;
                }

                $currentGroup = [
                    'type'  => $type,
                    'lines' => [$trimmedLine],
                ];
            } elseif ($currentGroup !== null) {
                // Продолжение текущей группы
                $currentGroup['lines'][] = $trimmedLine;
            }
        }

        if ($currentGroup !== null) {
            $groups[] = $currentGroup;
        }

        return $groups;
    }

    /**
     * Process lines group into message
     *
     * @param array{type: LineTypeEnum, lines: string[]} $group
     *
     * @return array
     */
    private function processGroup(array $group): array
    {
        return match ($group['type']) {
            LineTypeEnum::SYSTEM  => $this->processSystemGroup($group['lines']),
            LineTypeEnum::MESSAGE => $this->processMessageGroup($group['lines']),
            default               => throw new InvalidArgumentException('Cannot process continuation group'),
        };
    }

    /**
     * Process group that is a system message
     *
     * @param string[] $lines
     *
     * @return array
     */
    private function processSystemGroup(array $lines): array
    {
        $firstLine = $lines[0];
        $data      = $this->contentParser->parseSystemLine($firstLine);

        if ($data === null) {
            throw new RuntimeException('Failed to parse system line');
        }

        $system = $this->messageBuilder->createSystemMessage($data);

        return $this->messageBuilder->finalizeSystem($system);
    }

    /**
     * Process group that is a message
     *
     * @param string[] $lines
     *
     * @return array
     */
    private function processMessageGroup(array $lines): array
    {
        $firstLine = $lines[0];
        $restLines = array_slice($lines, 1);

        $data = $this->contentParser->parseMessageLine($firstLine);

        if ($data === null) {
            throw new RuntimeException('Failed to parse message line');
        }

        $draft = $this->messageBuilder->createDraftFromMessageData($data);

        $allLines = [$data['firstLine'], ...$restLines];

        return $this->messageBuilder->finalizeDraft($draft, $allLines);
    }
}
