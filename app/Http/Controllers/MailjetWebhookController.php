<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessMailjetWebhook;

class MailjetWebhookController extends Controller
{
    /**
     * Handle incoming Mailjet webhooks (Events API or Parse API)
     * Validates optional HMAC header using MAILJET_WEBHOOK_SECRET and stores payload
     */
    public function handle(Request $request)
    {
        try {
            $raw = $request->getContent();
            $headers = $request->headers->all();

            $secret = env('MAILJET_WEBHOOK_SECRET');

            if ($secret) {
                $sig = $request->header('X-Mailjet-Signature') ?? $request->header('X-Signature') ?? $request->header('X-Mailjet-Signature-HMAC-SHA256');

                if (!$sig) {
                    Log::warning('Mailjet webhook received without signature header. Rejecting.');
                    return response()->json(['status' => 'forbidden', 'message' => 'Missing signature'], 403);
                }

                $computed = hash_hmac('sha256', $raw, $secret);
                if (!hash_equals($computed, $sig)) {
                    Log::warning('Mailjet webhook signature mismatch. Rejecting.');
                    return response()->json(['status' => 'forbidden', 'message' => 'Invalid signature'], 403);
                }
            } else {
                Log::info('Mailjet webhook received but no MAILJET_WEBHOOK_SECRET configured; accepting payload (insecure).');
            }

            // Store raw payload for async processing
            $id = DB::table('mailjet_webhooks')->insertGetId([
                'payload' => $raw,
                'headers' => json_encode($headers),
                'processed' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Dispatch async job to process webhook
            ProcessMailjetWebhook::dispatch($id)->onQueue('mailjet-webhooks');

            return response()->json(['status' => 'ok'], 200);
        } catch (\Exception $e) {
            Log::error('Error in Mailjet webhook handler: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
