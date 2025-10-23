<?php

namespace App\Console\Commands\Mailbox;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurgeAllMessagesCommand extends Command
{
    protected $signature = 'mailbox:purge-all';

    protected $description = 'Deletes all messages and related data from the mailbox tables and storage.';

    public function handle(): int
    {
        if (! $this->confirm('This will permanently delete all mailbox data (database records and stored attachments). Do you want to continue?')) {
            $this->info('Operation cancelled.');

            return 0;
        }

        $this->info('Purging mailbox data...');

        // Using truncate for efficiency
        DB::table('mailbox_notes')->truncate();
        DB::table('mailbox_events')->truncate();
        DB::table('mailbox_attachments')->truncate();
        DB::table('mailbox_recipients')->truncate();
        DB::table('mailbox_messages')->truncate();

        $disk = config('mailbox.storage.disk');
        $files = Storage::disk($disk)->allFiles('mailbox');
        Storage::disk($disk)->delete($files);

        $this->info('Mailbox database tables and stored attachment files have been purged.');

        return 0;
    }
}
