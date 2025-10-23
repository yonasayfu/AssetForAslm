<?php

namespace App\Console\Commands;

use App\Jobs\Mailbox\IngestMailpitMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class MailpitSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailpit:sync {--since=} {--limit=50}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill Mailpit messages via HTTP API to ensure the mailbox stays in sync.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $since = $this->option('since');
        $baseUrl = rtrim(config('mailbox.mailpit_http_url'), '/');
        $page = 1;
        $totalQueued = 0;

        if (! $baseUrl) {
            $this->error('MAILPIT_HTTP_URL is not configured.');

            return self::FAILURE;
        }

        $this->info("Syncing messages from {$baseUrl}...");

        do {
            $response = Http::timeout(30)->get("{$baseUrl}/api/v1/messages", array_filter([
                'page' => $page,
                'limit' => $limit,
                'since' => $since,
            ]));

            if (! $response->successful()) {
                $this->error("Request failed ({$response->status()}): {$response->body()}");

                return self::FAILURE;
            }

            $data = $response->json();
            $messages = $data['messages'] ?? $data ?? [];

            foreach ($messages as $payload) {
                IngestMailpitMessage::dispatch($payload, $payload['mailbox']['label'] ?? 'sync')
                    ->onQueue(config('mailbox.queue'));
                $totalQueued++;
            }

            $page++;
        } while (count($messages) === $limit);

        $this->info("Queued {$totalQueued} messages for ingestion.");

        return self::SUCCESS;
    }
}
