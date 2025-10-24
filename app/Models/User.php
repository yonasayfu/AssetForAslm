<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Lab404\Impersonate\Models\Impersonate;
use Lab404\Impersonate\Services\ImpersonateManager;

class User extends Authenticatable
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    public const TYPE_EXTERNAL = 'external';
    public const TYPE_INTERNAL = 'internal';

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Impersonate;
    use Notifiable;
    use RecordsActivity;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'recovery_email',
        'phone_number',
        'account_status',
        'account_type',
        'approved_at',
        'approved_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $appends = [
        'two_factor_secret',
        'two_factor_recovery_codes',
        'is_impersonating',
        'impersonated_by_name',
    ];
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_email_recovery_codes',
        'remember_token',
    ];

    protected string $activityLogLabel = 'User';

    protected array $activityLogAttributes = [
        'name',
        'email',
        'account_status',
        'account_type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function getIsImpersonatingAttribute(): bool
    {
        /** @var ImpersonateManager $manager */
        $manager = app(ImpersonateManager::class);

        return $manager->isImpersonating();
    }

    public function getImpersonatedByNameAttribute(): ?string
    {
        /** @var ImpersonateManager $manager */
        $manager = app(ImpersonateManager::class);

        if (!$manager->isImpersonating() || auth()->id() !== $this->getKey()) {
            return null;
        }

        $impersonatorId = $manager->getImpersonatorId();

        if (!$impersonatorId) {
            return null;
        }

        static $impersonatorName;

        if ($impersonatorName === null) {
            $impersonatorName = static::query()
                ->select('name')
                ->find($impersonatorId)
                ?->name;
        }

        return $impersonatorName;
    }

    /**
     * Get the staff profile associated with the user.
     */
    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(UserNotificationPreference::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(self::class, 'approved_by');
    }

    public function getTwoFactorEmailRecoveryCodesAttribute(?string $value): array
    {
        if (! $value) {
            return [];
        }

        try {
            $decoded = json_decode(decrypt($value), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(
            array_filter($decoded, fn ($code) => is_string($code) && $code !== '')
        );
    }

    public function setTwoFactorEmailRecoveryCodesAttribute(?array $codes): void
    {
        if (empty($codes)) {
            $this->attributes['two_factor_email_recovery_codes'] = null;

            return;
        }

        $normalized = array_values(
            array_filter($codes, fn ($code) => is_string($code) && $code !== '')
        );

        if (! $normalized) {
            $this->attributes['two_factor_email_recovery_codes'] = null;

            return;
        }

        try {
            $encoded = json_encode($normalized, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $encoded = json_encode($normalized);
        }

        $this->attributes['two_factor_email_recovery_codes'] = encrypt($encoded);
    }
}
