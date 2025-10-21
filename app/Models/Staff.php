<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Staff extends Model
{
    use HasFactory;
    use RecordsActivity;

    protected $table = 'staff';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'job_title',
        'status',
        'hire_date',
        'user_id',
        'avatar_path',
    ];

    protected $casts = [
        'hire_date' => 'date',
    ];

    protected $appends = [
        'full_name',
        'avatar_url',
    ];

    protected string $activityLogLabel = 'Staff';

    protected array $activityLogAttributes = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'job_title',
        'status',
        'hire_date',
        'user_id',
        'avatar_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar_path) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar_path);
    }
}
