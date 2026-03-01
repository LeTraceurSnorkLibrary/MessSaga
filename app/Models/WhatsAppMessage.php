<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Модель для сообщений WhatsApp.
 * Наследует базовые поля от Message и добавляет специфичные поля WhatsApp.
 */
class WhatsAppMessage extends Message
{
    /**
     * @inheritdoc
     */
    protected $table = 'whatsapp_messages';

    /**
     * @inheritdoc
     *
     * Расширяем базовые fillable полями, специфичными для WhatsApp.
     */
    protected $fillable = [
        'conversation_id',
        'sender_name',
        'sender_external_id',
        'sent_at',
        'text',
        'message_type', // text, media, system
        'media_file',
        'raw',
    ];

    /**
     * @inheritdoc
     *
     * Расширяем базовые casts полями, специфичными для WhatsApp.
     */
    protected $casts = [
        'sent_at' => 'datetime',
        'raw'     => 'array',
    ];

    /**
     * @inheritdoc
     */
    protected $attributes = [
        'external_id' => null,
    ];
}
