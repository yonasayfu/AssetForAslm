<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'causer_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Convenience helper for storing a new activity log entry.
     */
    public static function record(
        ?int $causerId,
        Model $subject,
        string $action,
        ?string $description = null,
        ?array $changes = null
    ): self {
        return static::create([
            'causer_id' => $causerId,
            'action' => $action,
            'description' => $description ?? static::defaultDescription($subject, $action),
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'changes' => $changes,
        ]);
    }

    protected static function defaultDescription(Model $subject, string $action): string
    {
        return sprintf('%s %s', class_basename($subject), $action);
    }
}
