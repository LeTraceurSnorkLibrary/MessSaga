<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'messenger_account_id',
        'external_id',
        'title',
        'participants',
    ];

    protected $casts = [
        'participants' => 'array',
    ];

    public function messengerAccount(): BelongsTo
    {
        return $this->belongsTo(MessengerAccount::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
