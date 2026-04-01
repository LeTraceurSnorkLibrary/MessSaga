<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Абстрактный базовый класс для всех типов сообщений мессенджеров.
 * Содержит общие поля и методы, специфичные поля определяются в дочерних классах.
 */
abstract class Message extends Model
{
    use HasFactory;
    use HasEncryptedAttributes;

    /**
     * @inheritdoc
     *
     *  Базовые fillable поля, общие для всех типов сообщений.
     *  Дочерние классы могут расширять этот массив.
     */
    protected $fillable = [
        'conversation_id',
        'external_id',
        'sender_name',
        'sender_external_id',
        'sent_at',
        'text',
        'dedup_hash',
        'message_type',
        'raw',
        'media_attachment_id',
    ];

    /**
     * @inheritdoc
     *
     * Базовые casts, общие для всех типов сообщений.
     * Дочерние классы могут расширять этот массив.
     */
    protected $casts = [
        'raw'     => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Отношение к переписке.
     *
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * @return BelongsTo
     */
    public function mediaAttachment(): BelongsTo
    {
        return $this->belongsTo(MediaAttachment::class, 'media_attachment_id');
    }

    /**
     * @inheritdoc
     */
    protected static function booted(): void
    {
        static::deleting(function (Message $message): void {
            if ($message->media_attachment_id !== null) {
                MediaAttachment::query()->whereKey($message->media_attachment_id)->delete();
            }
        });
    }

    /**
     * @inheritdoc
     */
    protected function getEncryptedAttributes(): array
    {
        return ['text'];
    }
}
