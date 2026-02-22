<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection as SupportCollection;

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

    /**
     * Получить сообщения в зависимости от типа мессенджера.
     * Используется для универсального доступа к сообщениям.
     *
     * @return EloquentCollection|SupportCollection
     */
    public function messages(): EloquentCollection|SupportCollection
    {
        // Загружаем messengerAccount если еще не загружен
        if (!$this->relationLoaded('messengerAccount')) {
            $this->load('messengerAccount');
        }

        $messengerType = $this->messengerAccount->type ?? null;

        if (!$messengerType) {
            return collect(); // Пустая коллекция если тип неизвестен
        }

        return match ($messengerType) {
            'telegram' => $this->telegramMessages()->get(),
            'whatsapp' => $this->whatsappMessages()->get(),
            'viber'    => $this->viberMessages()->get(),
            default    => collect(),
        };
    }

    /**
     * Получить query builder для сообщений в зависимости от типа мессенджера.
     * Используется для построения запросов (with, where и т.д.).
     *
     * @return HasMany
     */
    public function messagesQuery(): HasMany
    {
        // Загружаем messengerAccount если еще не загружен
        if (!$this->relationLoaded('messengerAccount')) {
            $this->load('messengerAccount');
        }

        $messengerType = $this->messengerAccount->type ?? null;

        if (!$messengerType) {
            // Возвращаем пустой query builder
            return TelegramMessage::whereRaw('1 = 0');
        }

        return match ($messengerType) {
            'telegram' => $this->telegramMessages(),
            'whatsapp' => $this->whatsappMessages(),
            'viber'    => $this->viberMessages(),
            default    => TelegramMessage::whereRaw('1 = 0'),
        };
    }
}
