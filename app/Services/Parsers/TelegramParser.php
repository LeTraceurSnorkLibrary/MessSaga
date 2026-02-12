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

            $text = $msg['text'] ?? '';
            if (is_array($text)) {
                // В экспорте телеги text может быть массивом объектов/строк
                $text = collect($text)
                    ->map(fn ($part) => is_array($part) ? ($part['text'] ?? '') : $part)
                    ->join('');
            }

            $messages[] = [
                'external_id' => $msg['id'] ?? null,
                'sender_name' => $msg['from'] ?? null,
                'sender_external_id' => $msg['from_id'] ?? null,
                'sent_at' => $msg['date'] ?? null,
                'text' => $text,
                'raw' => $msg,
            ];
        }

        return [$conversation, $messages];
    }
}

