<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int                             $id
 * @property int                             $user_id
 * @property string                          $type
 * @property string                          $message
 * @property array<string, mixed>|null       $payload
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class UserNotification extends Model
{
    public const string TYPE_IMPORT_MEDIA_SKIPPED_QUOTA = 'import_media_skipped_quota';

    public const string TYPE_MEDIA_UPLOAD_SKIPPED_QUOTA = 'media_upload_skipped_quota';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'payload',
        'read_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'read_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
