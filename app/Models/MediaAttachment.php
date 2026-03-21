<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Файл медиа, привязанный к переписке; сообщение ссылается на запись по media_attachment_id.
 */
class MediaAttachment extends Model
{
    protected $fillable = [
        'conversation_id',
        'stored_path',
        'export_path',
        'mime_type',
        'original_filename',
    ];

    /**
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Данные для API (вложение в объект сообщения).
     *
     * @return array<string, mixed>
     */
    public function toApiArray(int $conversationId, int $messageId): array
    {
        $hasFile = $this->stored_path !== null && $this->stored_path !== '';

        return [
            'id'                => $this->id,
            'url'               => $hasFile
                ? route('api.conversations.messages.attachment', [
                    'conversation' => $conversationId,
                    'messageId'    => $messageId,
                ])
                : null,
            'export_path'       => $this->export_path,
            'mime_type'         => $this->mime_type,
            'original_filename' => $this->original_filename,
            'is_loaded'         => $hasFile,
            'is_image'          => $this->mime_type !== null && str_starts_with($this->mime_type, 'image'),
        ];
    }
}
