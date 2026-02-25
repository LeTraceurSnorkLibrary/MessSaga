<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WhatsAppParser extends AbstractParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public const PARSER_CORRESPONDING_MESSAGE_MODEL = WhatsAppMessage::class;

    /**
     * Формат даты в экспорте сообщений WhatsApp
     */
    private const EXPORT_MESSAGES_DATE_FORMAT = 'd.m.Y H:i';

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

        $participants     = $this->extractParticipants($lines);
        $conversationData = $this->buildConversationData($lines, $participants);
        $messages         = $this->parseMessages($lines);

        return new ConversationImportDTO($conversationData, $messages);
    }

    private function extractParticipants(array $lines): array
    {
        $participants = [];

        foreach ($lines as $line) {
            if ($this->isMessageLine($line, $matches)) {
                $sender                = trim($matches[3]);
                $participants[$sender] = true;
            }
        }

        return array_keys($participants);
    }

    private function buildConversationData(array $lines, array $participants): array
    {
        $contactName = null;
        $phoneNumber = null;

        // Ищем первого отправителя, чтобы определить тип чата
        foreach ($lines as $line) {
            if ($this->isMessageLine($line, $matches)) {
                $sender = trim($matches[3]);

                if ($this->isPhoneNumber($sender)) {
                    $phoneNumber = $this->normalizePhone($sender);
                } else {
                    $contactName = $sender;
                    break;
                }
            }
        }

        $title       = $contactName ?? $phoneNumber ?? 'WhatsApp chat';
        $accountName = $contactName
            ? "WhatsApp: {$contactName}"
            : ($phoneNumber
                ? "WhatsApp: {$phoneNumber}"
                : 'WhatsApp');

        return [
            'external_id'  => $phoneNumber,
            'title'        => $title,
            'participants' => $participants,
            'account_name' => $accountName,
            'account_meta' => [
                'phone_number' => $phoneNumber,
                'contact_name' => $contactName,
                'type'         => 'personal_chat',
            ],
        ];
    }

    private function parseMessages(array $lines): array
    {
        $messages       = [];
        $currentMessage = null;
        $messageLines   = [];

        foreach ($lines as $line) {
            $trimmedLine = rtrim($line, "\r");

            // Если строка начинается с даты - это новое сообщение
            if ($this->isMessageLine($trimmedLine, $matches) || $this->isSystemLine($trimmedLine, $systemMatches)) {

                // Сохраняем предыдущее сообщение, если оно было
                if ($currentMessage !== null) {
                    // Склеиваем все строки сообщения
                    $currentMessage['text'] = implode("\n", $messageLines);
                    $messages[]             = $this->finalizeMessage($currentMessage);
                }

                // Начинаем новое сообщение
                if (isset($matches)) {
                    $currentMessage = $this->createMessage($matches);
                    $messageLines   = [trim($matches[4])]; // Первая строка текста
                    $matches        = null;
                } else {
                    $currentMessage = $this->createSystemMessage($systemMatches);
                    $messageLines   = [trim($systemMatches[3])];
                    $systemMatches  = null;
                }

            } else {
                // Это продолжение текущего сообщения
                if ($currentMessage !== null) {
                    $messageLines[] = $trimmedLine;
                }
            }
        }

        // Сохраняем последнее сообщение
        if ($currentMessage !== null) {
            $currentMessage['text'] = implode("\n", $messageLines);
            $messages[]             = $this->finalizeMessage($currentMessage);
        }

        return $messages;
    }

    private function isMessageLine(string $line, ?array &$matches = null): bool
    {
        $pattern = '/^(\d{2}\.\d{2}\.\d{4}), (\d{2}:\d{2}) - ([^:]+): ?(.*)$/u';

        if (preg_match($pattern, $line, $found)) {
            $matches = $found;

            return true;
        }

        return false;
    }

    private function isSystemLine(string $line, ?array &$matches = null): bool
    {
        $pattern = '/^(\d{2}\.\d{2}\.\d{4}), (\d{2}:\d{2}) - (.+)$/u';

        if (preg_match($pattern, $line, $found)) {
            // Убеждаемся, что это не сообщение (нет двоеточия после отправителя)
            if (!str_contains($found[3], ': ')) {
                $matches = $found;

                return true;
            }
        }

        return false;
    }

    private function isPhoneNumber(string $value): bool
    {
        return (bool)preg_match('/^\+?\d[\d\s\-]{7,}\d$/', $value);
    }

    private function createMessage(array $matches): array
    {
        [, $date, $time, $sender, $firstLine] = $matches;

        $message = [
            'external_id'        => null,
            'sender_name'        => trim($sender),
            'sender_external_id' => trim($sender),
            'sent_at'            => Carbon::createFromFormat(self::EXPORT_MESSAGES_DATE_FORMAT, "{$date} {$time}"),
            'text'               => null, // Заполним позже, когда соберём все строки
            'message_type'       => 'text',
            'raw'                => [
                'date'   => $date,
                'time'   => $time,
                'sender' => trim($sender),
            ],
        ];

        // Определяем тип сообщения по первой строке
        if (Str::contains($firstLine, ['(файл добавлен)', '‎IMG-', '‎VID-', '‎AUD-'])) {
            $message['message_type'] = 'media';
        }

        return $message;
    }

    private function createSystemMessage(array $matches): array
    {
        [, $date, $time, $text] = $matches;

        return [
            'external_id'        => null,
            'sender_name'        => 'System',
            'sender_external_id' => null,
            'sent_at'            => Carbon::createFromFormat(self::EXPORT_MESSAGES_DATE_FORMAT, "{$date} {$time}"),
            'text'               => null, // Заполним позже
            'message_type'       => 'system',
            'raw'                => [
                'date' => $date,
                'time' => $time,
            ],
        ];
    }

    private function finalizeMessage(array $message): array
    {
        // Если это медиа-сообщение, очищаем текст от маркеров
        if ($message['message_type'] === 'media' && isset($message['text'])) {
            $message['text'] = preg_replace(
                ['/\s*\(файл добавлен\)$/m', '/^‎/u'],
                ['', ''],
                $message['text']
            );

            // Извлекаем имя файла из текста
            if (preg_match('/([^\/\\\]+\.\w+)/u', $message['text'], $fileMatches)) {
                $message['media_file'] = $fileMatches[1];
            }
        }

        // Очищаем текст от лишних пробелов по краям
        if (isset($message['text'])) {
            $message['text'] = trim($message['text']);
        }

        return $message;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone);
    }
}
