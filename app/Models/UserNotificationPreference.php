<?php

namespace App\Models;

use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasFactory;
    use RecordsActivity;

    protected $fillable = [
        'user_id',
        'notification_type',
        'channel',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    protected string $activityLogLabel = 'Notification Preference';

    protected array $activityLogAttributes = [
        'notification_type',
        'channel',
        'enabled',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
