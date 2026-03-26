<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Модель сообщений Telegram.
 */
class TelegramMessage extends Message
{
    /**
     * @inheritdoc
     */
    protected $table = 'telegram_messages';

    /**
     * @inheritdoc
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
     */
    protected $casts = [
        'raw'     => 'array',
        'sent_at' => 'datetime',
    ];
}
