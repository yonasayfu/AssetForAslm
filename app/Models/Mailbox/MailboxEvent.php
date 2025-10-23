<?php

namespace App\Models\Mailbox;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class MailboxEvent extends Model
{
    use HasFactory;
    use HasUlids;

    public const UPDATED_AT = null;

    protected $table = 'mailbox_events';

    protected $guarded = [];

    protected $casts = [
        'payload' => AsArrayObject::class,
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MailboxMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
