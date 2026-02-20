<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Модель для сообщений Telegram.
 * Наследует базовые поля от Message и добавляет специфичные поля Telegram.
 */
class TelegramMessage extends Message
{
    protected $table = 'telegram_messages';

    /**
     * @inheritdoc
     *
     * Расширяем базовые fillable полями, специфичными для Telegram.
     * В Laravel fillable не наследуется автоматически, поэтому указываем все поля явно.
     */
    protected $fillable = [
        // Базовые поля (из Message)
        'conversation_id',
        'external_id',
        'sender_name',
        'sender_external_id',
        'sent_at',
        'text',
        'message_type',
        'raw',

        // Специфичные поля Telegram
        'sticker_id',
        'sticker_set_name',
        'voice_duration',
        'voice_file_id',
        'video_file_id',
        'video_duration',
        'video_thumbnail_id',
        'photo_file_id',
        'photo_sizes',
        'gif_file_id',
        'gif_thumbnail_id',
        'document_file_id',
        'document_file_name',
        'document_mime_type',
        'document_file_size',
        'service_action',
        'service_actor',
        'forwarded_from_chat_id',
        'forwarded_from_message_id',
        'forwarded_from_name',
        'edited_at',
        'reactions',
    ];

    /**
     * @inheritdoc
     *
     * Расширяем базовые casts полями, специфичными для Telegram.
     */
    protected $casts = [
        'raw'           => 'array',
        'sent_at'       => 'datetime',
        'edited_at'     => 'datetime',
        'photo_sizes'   => 'array',
        'service_actor' => 'array',
        'reactions'     => 'array',
    ];
}
