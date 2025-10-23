<?php

namespace App\Models\Mailbox;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEncryptedString;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailboxMessage extends Model
{
    use HasFactory;
    use HasUlids;

    protected $table = 'mailbox_messages';

    protected $guarded = [];

    protected $casts = [
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'headers' => AsEncryptedArrayObject::class,
        'html_body' => AsEncryptedString::class,
        'text_body' => AsEncryptedString::class,
        'meta' => AsArrayObject::class,
    ];

    public function recipients(): HasMany
    {
        return $this->hasMany(MailboxRecipient::class, 'message_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MailboxAttachment::class, 'message_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(MailboxEvent::class, 'message_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(MailboxNote::class, 'message_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
