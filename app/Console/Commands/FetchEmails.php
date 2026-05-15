<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailChatService;
use Illuminate\Support\Facades\Log;

class FetchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch {--force : Force a larger fetch window}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch emails from IMAP and insert into chattbl (scheduled)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('IMAP sync is deprecated. This command now logs the deprecation and exits.');
        $force = $this->option('force') ? true : false;

        Log::warning('Scheduled emails:fetch invoked, but IMAP sync is deprecated. Use Mailjet webhooks for inbound mail.');

        return 0;
    }
}
