<?php

namespace App\Jobs\Mailbox;

use App\Models\Mailbox\MailboxMessage;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IngestMailpitMessage implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $uniqueFor = 60;

    public function __construct(
        protected array $payload,
        protected string $environment = 'default'
    ) {
        $this->onQueue(config('mailbox.queue'));
    }

    public function uniqueId(): string
    {
        return $this->payload['id'] ?? Str::uuid()->toString();
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $message = MailboxMessage::updateOrCreate(
                ['mailpit_id' => $this->payload['id']],
                $this->messageAttributes()
            );

            $this->syncRecipients($message);
            $this->syncAttachments($message);

            $message->events()->create([
                'type' => 'ingested',
                'payload' => [
                    'environment' => $this->environment,
                    'mailpit_id' => $this->payload['id'],
                ],
            ]);
        });
    }

    protected function messageAttributes(): array
    {
        $text = $this->payload['text'] ?? null;
        $html = $this->payload['html'] ?? null;
        $previewSource = $text ?: ($html ? strip_tags($html) : null);

        return [
            'subject' => $this->payload['subject'] ?? null,
            'preview' => $previewSource ? Str::limit(trim($previewSource), 200) : null,
            'status' => 'new',
            'environment' => $this->environment,
            'received_at' => CarbonImmutable::parse($this->payload['created'] ?? now()),
            'size' => $this->payload['size'] ?? 0,
            'html_body' => $html,
            'text_body' => $text,
            'headers' => $this->payload['headers'] ?? [],
            'meta' => [
                'tags' => $this->payload['tags'] ?? [],
                'raw' => Arr::except($this->payload, ['html', 'text', 'headers', 'attachments']),
            ],
        ];
    }

    protected function syncRecipients(MailboxMessage $message): void
    {
        $message->recipients()->delete();

        $this->storeRecipient($message, 'from', $this->payload['from']);

        foreach (['to', 'cc', 'bcc'] as $type) {
            foreach ($this->payload[$type] ?? [] as $recipient) {
                $this->storeRecipient($message, $type, $recipient);
            }
        }
    }

    protected function storeRecipient(MailboxMessage $message, string $type, array $data): void
    {
        $email = $data['address'] ?? $data['email'] ?? null;

        if (! $email) {
            return;
        }

        $message->recipients()->create([
            'type' => $type,
            'email' => strtolower($email),
            'name' => $data['name'] ?? null,
        ]);
    }

    protected function syncAttachments(MailboxMessage $message): void
    {
        $remoteAttachments = $this->payload['attachments'] ?? [];

        foreach ($remoteAttachments as $attachment) {
            $attachmentId = $attachment['id'] ?? Str::uuid()->toString();
            $record = $message->attachments()->updateOrCreate(
                [
                    'mailpit_attachment_id' => $attachmentId,
                ],
                [
                    'filename' => $attachment['filename'] ?? 'attachment',
                    'disk' => config('mailbox.storage_disk', 'local'),
                    'path' => null,
                    'mime_type' => $attachment['content_type'] ?? null,
                    'size' => $attachment['size'] ?? 0,
                    'checksum' => null,
                ]
            );

            if (! empty($attachment['id'])) {
                DownloadMailpitAttachment::dispatch($message->mailpit_id, $attachmentId, $record->id)
                    ->onQueue(config('mailbox.queue'));
            }
        }

        // Clean attachments removed upstream
        $remoteIds = collect($remoteAttachments)->pluck('id')->filter();
        if ($remoteIds->isNotEmpty()) {
            $message->attachments()
                ->whereNotIn('mailpit_attachment_id', $remoteIds)
                ->delete();
        }
    }
}
