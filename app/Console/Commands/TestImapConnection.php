<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
// IMAP debug command deprecated. Use Mailjet webhooks instead.
// use Webklex\IMAP\Facades\Client; // deprecated
use Illuminate\Support\Facades\Log;

class TestImapConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:imap-connection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test IMAP connectivity using Webklex IMAP client for the "gmail" account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting IMAP connectivity test...');

        try {
            $this->info('Initializing IMAP client (account: gmail)...');
            $client = Client::account('gmail');

            $this->info('Attempting to connect...');
            try {
                $connected = $client->connect();
                $this->info('Connect returned: ' . ($connected ? 'true' : 'false'));
            } catch (\Throwable $e) {
                $this->error('Connect failed: ' . $e->getMessage());
                Log::error('TestImapConnection: connect failed: ' . $e->getMessage());
                return 1;
            }

            // Attempt to get INBOX folder count
            try {
                $folder = $client->getFolder('INBOX');
                $messages = $folder->messages()->all()->get();
                $count = $messages->count();
                $this->info("INBOX message count: {$count}");
            } catch (\Throwable $e) {
                $this->error('Failed to access INBOX or fetch messages: ' . $e->getMessage());
                Log::error('TestImapConnection: INBOX access failed: ' . $e->getMessage());
            }

            // Disconnect cleanly
            try {
                $client->disconnect();
                $this->info('Disconnected cleanly');
            } catch (\Throwable $e) {
                $this->warn('Disconnect warning: ' . $e->getMessage());
                Log::warning('TestImapConnection: disconnect warning: ' . $e->getMessage());
            }

            $this->info('IMAP connectivity test completed successfully');

            // Try invoking EmailChatService to fetch and store new emails
            try {
                $this->info('Invoking EmailChatService::fetchNewEmails()...');
                $service = new \App\Services\EmailChatService();
                $result = $service->fetchNewEmails();
                $this->line('Result: ' . json_encode($result));
                Log::info('TestImapConnection: fetchNewEmails result: ' . json_encode($result));
            } catch (\Throwable $e) {
                $this->error('Error calling fetchNewEmails: ' . $e->getMessage());
                Log::error('TestImapConnection: fetchNewEmails error: ' . $e->getMessage());
            }
            return 0;

        } catch (\Throwable $e) {
            $this->error('Unexpected error during IMAP test: ' . $e->getMessage());
            Log::error('TestImapConnection: unexpected error: ' . $e->getMessage() . ' trace: ' . substr($e->getTraceAsString(), 0, 2000));
            return 1;
        }
    }
}
