<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\WhatsAppMessage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class WhatsAppParser extends AbstractParser implements ParserInterface
{
    public const PARSER_CORRESPONDING_MESSAGE_MODEL = WhatsAppMessage::class;

    private const DATE_FORMAT = 'd.m.Y H:i';

    public function parse(string $path): ConversationImportDTO
    {
        $content = file_get_contents($path);

        if ($content === false) {
            return new ConversationImportDTO([], []);
        }

        $lines = collect(explode("\n", $content))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->toArray();

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
                    break; // Нашли имя, дальше можно не искать
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

        foreach ($lines as $line) {
            // Системное сообщение
            if ($this->isSystemLine($line, $matches)) {
                if ($currentMessage) {
                    $messages[]     = $this->finalizeMessage($currentMessage);
                    $currentMessage = null;
                }

                $messages[] = $this->createSystemMessage($matches);
                continue;
            }

            // Обычное сообщение
            if ($this->isMessageLine($line, $matches)) {
                if ($currentMessage) {
                    $messages[] = $this->finalizeMessage($currentMessage);
                }

                $currentMessage = $this->createMessage($matches);
                continue;
            }

            // Продолжение предыдущего сообщения
            if ($currentMessage) {
                $currentMessage['text'] .= "\n" . $line;
            }
        }

        if ($currentMessage) {
            $messages[] = $this->finalizeMessage($currentMessage);
        }

        return $messages;
    }

    private function isMessageLine(string $line, ?array &$matches = null): bool
    {
        $pattern = '/^(\d{2}\.\d{2}\.\d{4}), (\d{2}:\d{2}) - ([^:]+): ?(.*)$/u';

        return (bool)preg_match($pattern, $line, $matches);
    }

    private function isSystemLine(string $line, ?array &$matches = null): bool
    {
        $pattern = '/^(\d{2}\.\d{2}\.\d{4}), (\d{2}:\d{2}) - (.+)$/u';

        return (bool)preg_match($pattern, $line, $matches);
    }

    private function isPhoneNumber(string $value): bool
    {
        return (bool)preg_match('/^\+?\d[\d\s\-]{7,}\d$/', $value);
    }

    private function createMessage(array $matches): array
    {
        [, $date, $time, $sender, $text] = $matches;

        $message = [
            'external_id'        => null,
            'sender_name'        => trim($sender),
            'sender_external_id' => trim($sender),
            'sent_at'            => Carbon::createFromFormat(self::DATE_FORMAT, "{$date} {$time}"),
            'text'               => trim($text)
                ?: null,
            'message_type'       => 'text',
            'raw'                => ['line' => implode(' - ', [$date, $time, $sender, $text])],
        ];

        // Определяем тип сообщения
        if (Str::contains($text, ['(файл добавлен)', '‎IMG-', '‎VID-', '‎AUD-'])) {
            $message['message_type'] = 'media';
            $message['media_file']   = $this->extractFilename($text);
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
            'sent_at'            => Carbon::createFromFormat(self::DATE_FORMAT, "{$date} {$time}"),
            'text'               => trim($text),
            'message_type'       => 'system',
            'raw'                => ['line' => implode(' - ', [$date, $time, $text])],
        ];
    }

    private function finalizeMessage(array $message): array
    {
        if ($message['message_type'] === 'media' && isset($message['text'])) {
            $message['text'] = preg_replace(
                ['/\s*\(файл добавлен\)$/', '/^‎/'],
                ['', ''],
                $message['text']
            );
        }

        return $message;
    }

    private function extractFilename(string $text): ?string
    {
        if (preg_match('/([^\/\\\]+\.\w+)/u', $text, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function normalizePhone(string $phone): string
    {
        return preg_replace('/[^\d+]/', '', $phone);
    }
}
