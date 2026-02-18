<?php

namespace App\Services\Parsers;

use Illuminate\Support\Arr;

class TelegramParser
{
    /**
     * @return array{0: array<string,mixed>, 1: array<int, array<string,mixed>>}
     */
    public function parse(string $path): array
    {
        $raw = json_decode(file_get_contents($path), true);

        if (! is_array($raw)) {
            return [null, []];
        }

        // Структура под типичный telegram export JSON (можем доработать по фактическому формату позже)
        $conversation = [
            'external_id' => Arr::get($raw, 'id'),
            'title' => Arr::get($raw, 'name', 'Telegram chat'),
            'participants' => Arr::get($raw, 'participants', []),
            'account_name' => Arr::get($raw, 'dialog_name'),
            'account_meta' => [
                'type' => Arr::get($raw, 'type'),
            ],
        ];

        $messages = [];

        foreach (Arr::get($raw, 'messages', []) as $msg) {
            if (! is_array($msg)) {
                continue;
            }

            // Определяем тип сообщения
            $messageType = Arr::get($msg, 'type', 'text');
            if ($messageType === 'message' && isset($msg['media_type'])) {
                $messageType = $msg['media_type']; // sticker, voice_message, video_message, etc.
            }

            $text = $msg['text'] ?? '';
            if (is_array($text)) {
                // В экспорте телеги text может быть массивом объектов/строк
                $text = collect($text)
                    ->map(fn ($part) => is_array($part) ? ($part['text'] ?? '') : $part)
                    ->join('');
            }

            $messageData = [
                'external_id' => $msg['id'] ?? null,
                'sender_name' => $msg['from'] ?? null,
                'sender_external_id' => $msg['from_id'] ?? null,
                'sent_at' => $msg['date'] ?? null,
                'text' => $text,
                'message_type' => $messageType,
                'raw' => $msg,
            ];

            // Извлекаем специфичные поля Telegram в зависимости от типа сообщения
            if ($messageType === 'sticker' || isset($msg['sticker'])) {
                $messageData['sticker_id'] = Arr::get($msg, 'sticker.file_id');
                $messageData['sticker_set_name'] = Arr::get($msg, 'sticker.set_name');
            }

            if ($messageType === 'voice_message' || isset($msg['media_type']) && $msg['media_type'] === 'voice_message') {
                $messageData['voice_duration'] = Arr::get($msg, 'duration_seconds');
                $messageData['voice_file_id'] = Arr::get($msg, 'file');
            }

            if ($messageType === 'video_message' || isset($msg['media_type']) && $msg['media_type'] === 'video_message') {
                $messageData['video_file_id'] = Arr::get($msg, 'file');
                $messageData['video_duration'] = Arr::get($msg, 'duration_seconds');
            }

            if ($messageType === 'photo' || isset($msg['photo'])) {
                $messageData['photo_file_id'] = Arr::get($msg, 'photo');
            }

            // Сервисные сообщения (action)
            if (isset($msg['action'])) {
                $messageData['service_action'] = $msg['action'];
                $messageData['service_actor'] = [
                    'name' => $msg['from'] ?? null,
                    'id' => $msg['from_id'] ?? null,
                ];
            }

            // Пересылка сообщений
            if (isset($msg['forwarded_from'])) {
                $messageData['forwarded_from_name'] = Arr::get($msg, 'forwarded_from');
            }

            // Редактирование
            if (isset($msg['edited'])) {
                $messageData['edited_at'] = Arr::get($msg, 'edited');
            }

            $messages[] = $messageData;
        }

        return [$conversation, $messages];
    }
}

