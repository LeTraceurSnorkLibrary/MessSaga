<?php

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
    use HasFactory, HasEncryptedAttributes;

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
        'message_type',
        'raw',
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
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * @inheritdoc
     */
    protected function getEncryptedAttributes(): array
    {
        return ['text'];
    }
}
