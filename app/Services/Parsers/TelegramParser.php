<?php

declare(strict_types=1);

namespace App\Services\Parsers;

use App\DTO\ConversationImportDTO;
use App\Models\MessageTypes\TelegramMessageTypesEnum;
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

            $msg_type    = Arr::get($msg, 'type');
            $messageType = is_string($msg_type)
                ? TelegramMessageTypesEnum::fromExportType($msg_type)->value
                : null;

            $text = $msg['text'] ?? '';
            if (is_array($text)) {
                $text = collect($text)
                    ->map(fn($part) => is_array($part)
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

            // Путь к медиа в экспорте (для сопоставления при импорте архива и догрузке)
            $messageData['attachment_export_path'] = $this->getTelegramAttachmentExportPath($msg);

            $messages[] = $messageData;
        }

        return new ConversationImportDTO($conversationData, $messages);
    }

    /**
     * Возвращает путь к файлу медиа в экспорте (как в архиве Telegram).
     * Используется для копирования при импорте архива и при догрузке медиа.
     *
     * @param array<array-key, mixed> $msg
     *
     * @return string|null
     */
    private function getTelegramAttachmentExportPath(array $msg): ?string
    {
        $path = $msg['file'] ?? $msg['photo'] ?? null;
        if (is_string($path)) {
            return $path;
        }
        if (is_array($path)) {
            // иногда photo — массив размеров; берём первый или путь
            $first = reset($path);

            return is_string($first)
                ? $first
                : null;
        }
        $stickerPath = Arr::get($msg, 'sticker.file_id');
        if (is_string($stickerPath)) {
            return $stickerPath;
        }
        $documentPath = $msg['document'] ?? Arr::get($msg, 'document_file_id');
        if (is_string($documentPath)) {
            return $documentPath;
        }

        return null;
    }

}
