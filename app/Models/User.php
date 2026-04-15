<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRoleEnum;
use App\Tariffs\Contracts\TariffInterface;
use App\Tariffs\TariffCatalog;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements FilamentUser
{
    /**
     * @use HasFactory<UserFactory>
     */
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'tariff_code',
        'encryption_salt',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'encryption_salt',
        'password',
        'remember_token',
    ];

    /**
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * @param Panel $panel
     *
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(UserRoleEnum::ADMIN->value);
    }

    /**
     * @return HasMany<MessengerAccount, $this>
     */
    public function messengerAccounts(): HasMany
    {
        return $this->hasMany(MessengerAccount::class);
    }

    /**
     * @return TariffInterface
     */
    public function tariff(): TariffInterface
    {
        return TariffCatalog::forCode($this->tariff_code);
    }

    /**
     * @return bool
     */
    public function canUploadMedia(): bool
    {
        return $this->getMediaUploadBlockReason() === null;
    }

    public function getUsedMediaStorageBytes(): int
    {
        $sum = DB::table('media_attachments')
            ->join('conversations', 'conversations.id', '=', 'media_attachments.conversation_id')
            ->join('messenger_accounts', 'messenger_accounts.id', '=', 'conversations.messenger_account_id')
            ->where('messenger_accounts.user_id', $this->id)
            ->whereNotNull('media_attachments.stored_path')
            ->where('media_attachments.stored_path', '!=', '')
            ->sum('media_attachments.size_bytes');

        return (int)$sum;
    }

    public function getUsedMediaFilesCount(): int
    {
        return (int)DB::table('media_attachments')
            ->join('conversations', 'conversations.id', '=', 'media_attachments.conversation_id')
            ->join('messenger_accounts', 'messenger_accounts.id', '=', 'conversations.messenger_account_id')
            ->where('messenger_accounts.user_id', $this->id)
            ->whereNotNull('media_attachments.stored_path')
            ->where('media_attachments.stored_path', '!=', '')
            ->count();
    }

    public function getRemainingMediaStorageBytes(): int
    {
        $remaining = $this->tariff()->getMaxStorageBytes() - $this->getUsedMediaStorageBytes();

        return max(0, $remaining);
    }

    public function getRemainingMediaFilesCount(): int
    {
        $remaining = $this->tariff()->getMaxMediaFilesCount() - $this->getUsedMediaFilesCount();

        return max(0, $remaining);
    }

    public function getMediaUploadBlockReason(): ?string
    {
        if (!$this->tariff()->allowsMediaUpload()) {
            return 'tariff_media_disabled';
        }

        if ($this->getRemainingMediaFilesCount() <= 0) {
            return 'quota_files_exceeded';
        }

        if ($this->getRemainingMediaStorageBytes() <= 0) {
            return 'quota_storage_exceeded';
        }

        if ($this->getRemainingMediaFilesCount() <= 0) {
            return 'quota_files_exceeded';
        }

        return null;
    }

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user): void {
            if (empty($user->encryption_salt)) {
                $user->encryption_salt = base64_encode(random_bytes(32));
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
