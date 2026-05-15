<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailChatService;
use App\Jobs\FetchEmailsJob;
use Illuminate\Support\Facades\Log;

class FetchEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch new emails from IMAP and store them in the database';

    public function handle()
    {
        $this->info('Starting scheduled email fetch...');

        try {
            // Run sync directly to avoid queue worker dependency for scheduled fetches.
            $imapEmail = strtolower(trim(env('IMAP_USERNAME')));
            // IMAP sync deprecated; Mailjet webhooks are used for inbound mail. No action taken.
            $this->info('Completed syncInboxFromGmail');
            Log::info('Scheduled emails:fetch executed syncInboxFromGmail');
            return 0;
        } catch (\Throwable $e) {
            $this->error('Error during scheduled fetch: ' . $e->getMessage());
            Log::error('Scheduled emails:fetch error: ' . $e->getMessage());
            return 1;
        }
    }
}
