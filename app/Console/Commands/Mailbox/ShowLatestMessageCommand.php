<?php

namespace App\Console\Commands\Mailbox;

use App\Models\Mailbox\MailboxMessage;
use Illuminate\Console\Command;

class ShowLatestMessageCommand extends Command
{
    protected $signature = 'mailbox:show-latest';

    protected $description = 'Shows the latest captured email and extracts the password reset link.';

    public function handle(): int
    {
        $message = MailboxMessage::latest()->first();

        if (!$message) {
            $this->info('No messages found in the mailbox.');

            return 0;
        }

        $this->info("Latest Message Details:");
        $this->table(
            ['Subject', 'From', 'To', 'Received'],
            [
                [
                    $message->subject,
                    $message->from['address'] ?? 'N/A',
                    collect($message->recipients)->where('type', 'to')->pluck('address')->join(', '),
                    $message->created_at->toDateTimeString(),
                ],
            ]
        );

        $resetLink = $this->extractResetLink($message->html_body);

        if ($resetLink) {
            $this->info("\nPassword Reset Link:");
            $this->line($resetLink);
        } else {
            $this->warn("\nCould not find a password reset link in the message body.");
        }

        return 0;
    }

    private function extractResetLink(?string $htmlBody): ?string
    {
        if (!$htmlBody) {
            return null;
        }

        preg_match('/href="([^"]*\/reset-password\/[^"]*)"/', $htmlBody, $matches);

        return $matches[1] ?? null;
    }
}
