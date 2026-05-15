<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\EmailChatService;

class ProcessMailjetWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $webhookId;

    /**
     * Create a new job instance.
     * @param int $webhookId
     */
    public function __construct(int $webhookId)
    {
        $this->webhookId = $webhookId;
        $this->onQueue('mailjet-webhooks');
    }

    /**
     * Execute the job.
     */
    public function handle(EmailChatService $emailChatService)
    {
        try {
            $row = DB::table('mailjet_webhooks')->where('id', $this->webhookId)->first();
            if (!$row) {
                Log::warning('Mailjet webhook row not found: ' . $this->webhookId);
                return;
            }

            // Try to decode JSON payload
            $payload = json_decode($row->payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If raw payload isn't JSON (Parse API), try to use as URL-encoded form
                $payload = @json_decode(json_encode($row->payload), true);
            }

            // If payload is a string that contains JSON array (events API), decode again
            if (is_string($row->payload)) {
                $decoded = json_decode($row->payload, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $payload = $decoded;
                }
            }

            // If payload is empty, log and mark processed
            if (empty($payload)) {
                Log::warning('Empty or invalid Mailjet webhook payload for id ' . $this->webhookId);
                DB::table('mailjet_webhooks')->where('id', $this->webhookId)->update(['processed' => true, 'processed_at' => now(), 'updated_at' => now()]);
                return;
            }

            // Delegate to EmailChatService to handle inbound payloads and events
            $emailChatService->handleMailjetInbound($payload);

            DB::table('mailjet_webhooks')->where('id', $this->webhookId)->update(['processed' => true, 'processed_at' => now(), 'updated_at' => now()]);

            Log::info('Processed Mailjet webhook id: ' . $this->webhookId);
        } catch (\Exception $e) {
            Log::error('Error processing Mailjet webhook id ' . $this->webhookId . ': ' . $e->getMessage());
            // Do not mark as processed so it can be retried
            throw $e;
        }
    }
}
