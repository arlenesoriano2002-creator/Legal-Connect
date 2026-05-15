<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\EmailChatService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FetchEmailsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // no-op
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $service = new EmailChatService();
            $result = $service->fetchNewEmails();
            Log::info('FetchEmailsJob completed: ' . json_encode($result));

            // Record fetch result in the email_fetch_logs table
            try {
                DB::table('email_fetch_logs')->insert([
                    'ran_at' => Carbon::now(),
                    'success' => $result['success'] ?? false,
                    'count' => $result['count'] ?? null,
                    'message' => $result['message'] ?? null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to write email_fetch_logs: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error('FetchEmailsJob failed: ' . $e->getMessage());
        }
    }
}
