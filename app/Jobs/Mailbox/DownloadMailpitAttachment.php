<?php

namespace App\Jobs\Mailbox;

use App\Models\Mailbox\MailboxAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadMailpitAttachment implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $mailpitId,
        protected string $mailpitAttachmentId,
        protected string $attachmentRecordId
    ) {
        $this->onQueue(config('mailbox.queue'));
    }

    public function handle(): void
    {
        $attachment = MailboxAttachment::find($this->attachmentRecordId);

        if (! $attachment) {
            return;
        }

        $baseUrl = rtrim(config('mailbox.mailpit_http_url'), '/');
        $url = sprintf(
            '%s/api/v1/message/%s/attachment/%s',
            $baseUrl,
            $this->mailpitId,
            $this->mailpitAttachmentId
        );

        $response = Http::timeout(20)->get($url);

        if (! $response->successful()) {
            Log::warning('Failed to download Mailpit attachment.', [
                'url' => $url,
                'status' => $response->status(),
            ]);

            $this->release(30);

            return;
        }

        $contents = $response->body();
        $disk = $attachment->disk ?: config('mailbox.storage_disk', 'local');
        $safeFileName = $this->sanitizeFilename($attachment->filename ?? 'attachment.bin');
        $path = sprintf('mailbox/%s/%s-%s', $attachment->message_id, $this->mailpitAttachmentId, $safeFileName);

        Storage::disk($disk)->put($path, $contents);

        $attachment->update([
            'path' => $path,
            'size' => strlen($contents),
            'checksum' => hash('sha256', $contents),
        ]);
    }

    protected function sanitizeFilename(string $filename): string
    {
        return (string) Str::of($filename)
            ->replaceMatches('/[^\w.\-]/', '_')
            ->limit(120, '');
    }
}
