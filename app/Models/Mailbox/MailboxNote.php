<?php

namespace App\Models\Mailbox;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailboxNote extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'mailbox_notes';

    protected $guarded = [];

    public function message(): BelongsTo
    {
        return $this->belongsTo(MailboxMessage::class, 'message_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
