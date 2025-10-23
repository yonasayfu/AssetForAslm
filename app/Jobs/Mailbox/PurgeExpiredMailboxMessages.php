<?php

namespace App\Jobs\Mailbox;

use App\Models\Mailbox\MailboxMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredMailboxMessages implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->onQueue(config('mailbox.queue'));
    }

    public function handle(): void
    {
        $days = max(1, config('mailbox.retention_days', 7));
        $cutoff = now()->subDays($days);

        MailboxMessage::where('received_at', '<', $cutoff)
            ->orderBy('received_at')
            ->chunk(50, function ($messages) {
                foreach ($messages as $message) {
                    foreach ($message->attachments as $attachment) {
                        if ($attachment->path) {
                            Storage::disk($attachment->disk ?: config('mailbox.storage_disk', 'local'))
                                ->delete($attachment->path);
                        }
                    }

                    Log::info('Purging mailbox message', [
                        'mailpit_id' => $message->mailpit_id,
                        'message_id' => $message->id,
                    ]);

                    $message->delete();
                }
            });
    }
}
