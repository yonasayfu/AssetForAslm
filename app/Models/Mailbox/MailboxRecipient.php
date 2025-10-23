<?php

namespace App\Models\Mailbox;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailboxRecipient extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'mailbox_recipients';

    protected $guarded = [];

    protected $casts = [
        'type' => 'string',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MailboxMessage::class, 'message_id');
    }
}
