<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\TelegramMessage;
use Illuminate\Support\Arr;

class TelegramParser extends AbstractParser implements ParserInterface
{
    /**
     * @inheritdoc
     */
    public const PARSER_CORRESPONDING_MESSAGE_MODEL = TelegramMessage::class;

    /**
     * @inheritdoc
     */
    public function parse(string $path): ConversationImportDTO
    {
        $raw = json_decode(file_get_contents($path), true);

        if (!is_array($raw)) {
            return new ConversationImportDTO([], []);
        }

        $conversationData = [
            'external_id'  => sprintf('telegram_%1$s', Arr::get($raw, 'id')),
            'title'        => Arr::get($raw, 'name', 'Telegram chat'),
            'participants' => Arr::get($raw, 'participants', []),
            'account_name' => Arr::get($raw, 'name'),
            'account_meta' => [
                'type' => Arr::get($raw, 'type'),
            ],
        ];

        $messages = [];

        foreach (Arr::get($raw, 'messages', []) as $msg) {
            if (!is_array($msg)) {
                continue;
            }

            $messageType = Arr::get($msg, 'type', 'text');
            if ($messageType === 'message' && isset($msg['media_type'])) {
                $messageType = $msg['media_type'];
            }

            $text = $msg['text'] ?? '';
            if (is_array($text)) {
                $text = collect($text)
                    ->map(fn ($part) => is_array($part)
                        ? ($part['text'] ?? '')
                        : $part)
                    ->join('');
            }

            $messageData = [
                'external_id'        => $msg['id'] ?? null,
                'sender_name'        => $msg['from'] ?? null,
                'sender_external_id' => $msg['from_id'] ?? null,
                'sent_at'            => $msg['date'] ?? null,
                'text'               => $text,
                'message_type'       => $messageType,
                'raw'                => $msg,
            ];

            if ($messageType === 'sticker' || isset($msg['sticker'])) {
                $messageData['sticker_id']       = Arr::get($msg, 'sticker.file_id');
                $messageData['sticker_set_name'] = Arr::get($msg, 'sticker.set_name');
            }

            if ($messageType === 'voice_message' || (isset($msg['media_type']) && $msg['media_type'] === 'voice_message')) {
                $messageData['voice_duration'] = Arr::get($msg, 'duration_seconds');
                $messageData['voice_file_id']  = Arr::get($msg, 'file');
            }

            if ($messageType === 'video_message' || (isset($msg['media_type']) && $msg['media_type'] === 'video_message')) {
                $messageData['video_file_id']  = Arr::get($msg, 'file');
                $messageData['video_duration'] = Arr::get($msg, 'duration_seconds');
            }

            if ($messageType === 'photo' || isset($msg['photo'])) {
                $messageData['photo_file_id'] = Arr::get($msg, 'photo');
            }

            if (isset($msg['action'])) {
                $messageData['service_action'] = $msg['action'];
                $messageData['service_actor']  = [
                    'name' => $msg['from'] ?? null,
                    'id'   => $msg['from_id'] ?? null,
                ];
            }

            if (isset($msg['forwarded_from'])) {
                $messageData['forwarded_from_name'] = Arr::get($msg, 'forwarded_from');
            }

            if (isset($msg['edited'])) {
                $messageData['edited_at'] = $msg['edited'];
            }

            $messages[] = $messageData;
        }

        return new ConversationImportDTO($conversationData, $messages);
    }
}
