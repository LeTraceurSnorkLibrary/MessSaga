<?php

namespace App\Models;

use App\Models\Concerns\HasEncryptedAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory, HasEncryptedAttributes;

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
        'raw',
    ];

    /**
     * @inheritdoc
     */
    protected $casts = [
        'raw'     => 'array',
        'sent_at' => 'datetime',
    ];

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
