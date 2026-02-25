<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Модель для сообщений Viber.
 * Наследует базовые поля от Message и добавляет специфичные поля Viber.
 */
class ViberMessage extends Message
{
    /**
     * @inheritdoc
     */
    protected $table = 'viber_messages';

    /**
     * @inheritdoc
     *
     * Расширяем базовые fillable полями, специфичными для Viber.
     */
    protected $fillable = [
        // Базовые поля (наследуются от Message)
        'conversation_id',
        'external_id',
        'sender_name',
        'sender_external_id',
        'sent_at',
        'text',
        'message_type',
        'raw',

        // Специфичные поля Viber
        'media_url',
        'media_file_name',
        'media_mime_type',
        'media_file_size',
        'media_thumbnail_url',
        'video_duration',
        'latitude',
        'longitude',
        'contact_data',
        'sticker_id',
        'urls',
    ];

    /**
     * @inheritdoc
     *
     * Расширяем базовые casts полями, специфичными для Viber.
     */
    protected $casts = [
        'raw'          => 'array',
        'sent_at'      => 'datetime',
        'contact_data' => 'array',
        'urls'         => 'array',
    ];
}
