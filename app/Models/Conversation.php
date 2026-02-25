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
     * Returns hasMany for messages of passed $messageModel class.
     *
     * @template TRelatedModel of Model
     *
     * @param class-string<TRelatedModel> $messageModel
     *
     * @return HasMany<TRelatedModel, $this>
     */
    public function messages(string $messageModel): HasMany
    {
        return $this->hasMany($messageModel);
    }
}
