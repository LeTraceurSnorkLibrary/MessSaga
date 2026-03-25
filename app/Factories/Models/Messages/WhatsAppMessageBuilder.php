<?php

declare(strict_types=1);

namespace App\Factories\Models\Messages;

use App\Models\MessageTypes\WhatsAppMessageTypesEnum;
use Carbon\Carbon;

class WhatsAppMessageBuilder
{
    /**
     * Message's datetime format
     */
    private const DATETIME_FORMAT = 'd.m.Y H:i';

    /**
     * Indicators that message is of media-type
     */
    private const MEDIA_INDICATORS = ['(файл добавлен)'];

    /**
     * RegExp to detect filename added to media-typed-message
     */
    private const FILENAME_PATTERN = '/([^\/\\\]+\.\w+)/u';

    /**
     * Creates message draft from raw data
     *
     * @param array<string, string> $data
     *
     * @return array<string, mixed>
     */
    public function createDraftFromMessageData(array $data): array
    {
        $dateTime = Carbon::createFromFormat(
            self::DATETIME_FORMAT,
            "{$data['date']} {$data['time']}"
        );

        $messageType = $this->detectMessageType($data['firstLine']);
        $isMediaType = $messageType === WhatsAppMessageTypesEnum::MEDIA->value;
        $mediaFile   = $isMediaType
            ? $this->extractFilename($data['firstLine'])
            : null;

        return [
            'date'         => $data['date'],
            'time'         => $data['time'],
            'sender'       => $data['sender'],
            'sent_at'      => $dateTime,
            'message_type' => $messageType,
            'text'         => null,
            'raw'          => [
                'date'      => $data['date'],
                'time'      => $data['time'],
                'sender'    => $data['sender'],
                'firstLine' => $data['firstLine'],
            ],
        ];
    }

    /**
     * Creates system message
     *
     * @param array<string, string> $data
     *
     * @return array<string, mixed>
     */
    public function createSystemMessage(array $data): array
    {
        $dateTime = Carbon::createFromFormat(
            self::DATETIME_FORMAT,
            "{$data['date']} {$data['time']}"
        );

        return [
            'date'               => $data['date'],
            'time'               => $data['time'],
            'sender_name'        => 'System',
            'sender_external_id' => null,
            'sent_at'            => $dateTime,
            'text'               => $data['text'],
            'message_type'       => 'system',
            'raw'                => [
                'date' => $data['date'],
                'time' => $data['time'],
                'text' => $data['text'],
            ],
        ];
    }

    /**
     * Финальная обработка сообщения (склейка строк)
     *
     * @param array $draft
     * @param array $messageLines
     *
     * @return array<string, mixed>
     */
    public function finalizeDraft(array $draft, array $messageLines): array
    {
        $fullText = implode("\n", $messageLines);

        if ($draft['message_type'] === WhatsAppMessageTypesEnum::MEDIA->value) {
            $fullText = $this->cleanMediaText($fullText);
        }

        // Имя файла для сопоставления с медиа в архиве: из первой строки или из всего текста
        $mediaFile = null;
        if ($draft['message_type'] === WhatsAppMessageTypesEnum::MEDIA->value && $mediaFile === null) {
            $mediaFile = $this->extractFilename($fullText);
        }

        return [
            'sender_name'            => $draft['sender'],
            'sender_external_id'     => $draft['sender'],
            'sent_at'                => $draft['sent_at'],
            'text'                   => trim($fullText)
                ?: null,
            'message_type'           => $draft['message_type'],
            'attachment_export_path' => $mediaFile,
            'raw'                    => json_encode($draft['raw']),
        ];
    }

    /**
     * Преобразует системное сообщение в формат для вставки
     *
     * @param array<string, mixed> $system
     *
     * @return array<string, mixed>
     */
    public function finalizeSystem(array $system): array
    {
        return [
            'sender_name'        => 'System',
            'sender_external_id' => null,
            'sent_at'            => $system['sent_at'],
            'text'               => $system['text'],
            'message_type'       => 'system',
            'raw'                => json_encode($system['raw']),
        ];
    }

    /**
     * @param string $firstLine
     *
     * @return string
     */
    private function detectMessageType(string $firstLine): string
    {
        foreach (self::MEDIA_INDICATORS as $indicator) {
            if (str_contains($firstLine, $indicator)) {
                return WhatsAppMessageTypesEnum::MEDIA->value;
            }
        }

        return WhatsAppMessageTypesEnum::TEXT->value;
    }

    /**
     * @param string $text
     *
     * @return string|null
     */
    private function extractFilename(string $text): ?string
    {
        if (preg_match(self::FILENAME_PATTERN, $text, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function cleanMediaText(string $text): string
    {
        return str_replace(
            static::MEDIA_INDICATORS,
            array_fill(0, count(static::MEDIA_INDICATORS), ''),
            $text
        );
    }
}
