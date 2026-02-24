<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'messenger_account_id',
        'external_id',
        'title',
        'participants',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'participants' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function messengerAccount(): BelongsTo
    {
        return $this->belongsTo(MessengerAccount::class);
    }

    /**
     * Получить сообщения Telegram.
     *
     * @return HasMany
     */
    public function telegramMessages(): HasMany
    {
        return $this->hasMany(TelegramMessage::class);
    }

    /**
     * Получить сообщения WhatsApp.
     *
     * @return HasMany
     */
    public function whatsappMessages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    /**
     * Получить сообщения Viber.
     *
     * @return HasMany
     */
    public function viberMessages(): HasMany
    {
        return $this->hasMany(ViberMessage::class);
    }
}
