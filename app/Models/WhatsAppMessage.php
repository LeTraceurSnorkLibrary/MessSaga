<?php

namespace App\Models;

/**
 * Модель для сообщений WhatsApp.
 * Наследует базовые поля от Message и добавляет специфичные поля WhatsApp.
 */
class WhatsAppMessage extends Message
{
    protected $table = 'whatsapp_messages';

    /**
     * @inheritdoc
     *
     * Расширяем базовые fillable полями, специфичными для WhatsApp.
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

        // Специфичные поля WhatsApp
        'status',
        'status_updated_at',
        'is_forwarded',
        'forwarded_from_name',
        'forwarded_at',
        'voice_note_file_id',
        'voice_note_duration',
        'media_file_id',
        'media_file_name',
        'media_mime_type',
        'media_file_size',
        'media_caption',
        'media_thumbnail_id',
        'latitude',
        'longitude',
        'location_name',
        'location_address',
        'contact_data',
        'reactions',
        'mentions',
        'quoted_message_id',
        'quoted_text',
        'labels',
    ];

    /**
     * @inheritdoc
     *
     * Расширяем базовые casts полями, специфичными для WhatsApp.
     */
    protected $casts = [
        'raw'               => 'array',
        'sent_at'           => 'datetime',
        'status_updated_at' => 'datetime',
        'forwarded_at'      => 'datetime',
        'is_forwarded'      => 'boolean',
        'contact_data'      => 'array',
        'reactions'         => 'array',
        'mentions'          => 'array',
        'labels'            => 'array',
    ];
}
