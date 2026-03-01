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
     * @param array $data
     *
     * @return array
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
            'media_file'   => $mediaFile,
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
     * @param array $data
     *
     * @return array
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
            'media_file'         => null,
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
     * @return array
     */
    public function finalizeDraft(array $draft, array $messageLines): array
    {
        $fullText = implode("\n", $messageLines);

        if ($draft['message_type'] === WhatsAppMessageTypesEnum::MEDIA->value) {
            $fullText = $this->cleanMediaText($fullText);
        }

        // Имя файла для сопоставления с медиа в архиве: из первой строки или из всего текста
        $mediaFile = $draft['media_file'];
        if ($draft['message_type'] === WhatsAppMessageTypesEnum::MEDIA->value && $mediaFile === null) {
            $mediaFile = $this->extractFilename($fullText);
        }

        $out = [
            'sender_name'        => $draft['sender'],
            'sender_external_id' => $draft['sender'],
            'sent_at'            => $draft['sent_at'],
            'text'               => trim($fullText)
                ?: null,
            'message_type'       => $draft['message_type'],
            'media_file'         => $mediaFile,
            'raw'                => json_encode($draft['raw']),
        ];
        $out['attachment_export_path'] = $mediaFile;

        return $out;
    }

    /**
     * Преобразует системное сообщение в формат для вставки
     *
     * @param array $system
     *
     * @return array
     */
    public function finalizeSystem(array $system): array
    {
        return [
            'sender_name'        => 'System',
            'sender_external_id' => null,
            'sent_at'            => $system['sent_at'],
            'text'               => $system['text'],
            'message_type'       => 'system',
            'media_file'         => null,
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
