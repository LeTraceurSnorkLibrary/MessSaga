<?php

declare(strict_types=1);

namespace App\Services\Parsers\WhatsApp;

use Carbon\Carbon;

class MessageBuilder
{
    private const DATE_FORMAT      = 'd.m.Y H:i';
    private const MEDIA_INDICATORS = ['(файл добавлен)', '‎IMG-', '‎VID-', '‎AUD-'];
    private const FILENAME_PATTERN = '/([^\/\\\]+\.\w+)/u';

    /**
     * Создаёт черновик сообщения из сырых данных
     *
     * @param array $data
     *
     * @return array
     */
    public function createDraftFromMessageData(array $data): array
    {
        $dateTime = Carbon::createFromFormat(
            self::DATE_FORMAT,
            "{$data['date']} {$data['time']}"
        );

        $messageType = $this->detectMessageType($data['firstLine']);
        $mediaFile   = $messageType === 'media'
            ? $this->extractFilename($data['firstLine'])
            : null;

        return [
            'date'         => $data['date'],
            'time'         => $data['time'],
            'sender'       => $data['sender'],
            'sent_at'      => $dateTime,
            'message_type' => $messageType,
            'media_file'   => $mediaFile,
            'text'         => null, // будет заполнен позже
            'raw'          => [
                'date'      => $data['date'],
                'time'      => $data['time'],
                'sender'    => $data['sender'],
                'firstLine' => $data['firstLine'],
            ],
        ];
    }

    /**
     * Создаёт системное сообщение
     *
     * @param array $data
     *
     * @return array
     */
    public function createSystemMessage(array $data): array
    {
        $dateTime = Carbon::createFromFormat(
            self::DATE_FORMAT,
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

        // Для медиа-сообщений очищаем текст от маркеров
        if ($draft['message_type'] === 'media') {
            $fullText = $this->cleanMediaText($fullText);
        }

        return [
            'sender_name'        => $draft['sender'],
            'sender_external_id' => $draft['sender'],
            'sent_at'            => $draft['sent_at'],
            'text'               => trim($fullText)
                ?: null,
            'message_type'       => $draft['message_type'],
            'media_file'         => $draft['media_file'],
            'raw'                => json_encode($draft['raw']),
        ];
    }

    /**
     * Преобразует системное сообщение в формат для вставки
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

    private function detectMessageType(string $firstLine): string
    {
        foreach (self::MEDIA_INDICATORS as $indicator) {
            if (str_contains($firstLine, $indicator)) {
                return 'media';
            }
        }

        return 'text';
    }

    private function extractFilename(string $text): ?string
    {
        if (preg_match(self::FILENAME_PATTERN, $text, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function cleanMediaText(string $text): string
    {
        return preg_replace(
            ['/\s*\(файл добавлен\)$/m', '/^‎/u'],
            ['', ''],
            $text
        );
    }
}
