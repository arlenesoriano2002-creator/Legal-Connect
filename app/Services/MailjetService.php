<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MailjetService
{
    private $apiKey;
    private $secretKey;
    private $fromEmail;
    private $fromName;
    private $apiUrl = 'https://api.mailjet.com/v3.1/send';

    public function __construct()
    {
        // Load credentials - using provided Mailjet API keys
        // Production: Load from .env or config, but for this implementation using provided values
        $this->apiKey = env('MAILJET_API_KEY', 'e0fa4001b29ab0df49a2fbab6c9b3dac');
        $this->secretKey = env('MAILJET_SECRET_KEY', '7bdd0503cab0849ad5647a568da38b02');
        
        // Use env variables for sender email/name with fallback
        $this->fromEmail = env('MAIL_FROM_ADDRESS', 'legalconnect681@gmail.com');
        $this->fromName = env('MAIL_FROM_NAME', 'LegalConnect');

        try {
            Log::info('✅ MailjetService initialized with API key: ' . substr($this->apiKey, 0, 8) . '...');
        } catch (\Exception $e) {
            // Log facade not available during standalone execution - silently continue
            error_log('MailjetService initialized with API key: ' . substr($this->apiKey, 0, 8) . '...');
        }
    }

    /**
     * Send email using Mailjet v3.1 API
     * 
     * @param array $toList Email addresses to send to
     * @param string $subject Email subject
     * @param string $textPart Plain text body
     * @param string|null $htmlPart HTML body (optional)
     * @param array $attachments Attachments array with ContentType, Filename, Base64Content
     * @param string|null $customId Custom ID for event tracking
     * @return array Success/failure response
     */
    public function sendMessage($toList, $subject, $textPart, $htmlPart = null, $attachments = [], $customId = null)
    {
        try {
            Log::info("📤 MailjetService::sendMessage called with recipients: " . implode(', ', $toList));

            // Build recipient array
            $recipients = [];
            foreach ($toList as $email) {
                $recipients[] = [
                    'Email' => trim($email),
                    'Name' => ''
                ];
            }

            // Build message payload (Mailjet v3.1 format)
            $payload = [
                'Messages' => [
                    [
                        'From' => [
                            'Email' => $this->fromEmail,
                            'Name' => $this->fromName
                        ],
                        'To' => $recipients,
                        'Subject' => $subject,
                        'TextPart' => $textPart,
                    ]
                ]
            ];

            // Add HTML part if provided
            if ($htmlPart) {
                $payload['Messages'][0]['HTMLPart'] = $htmlPart;
            }

            // Add attachments if provided
            if (!empty($attachments)) {
                $payload['Messages'][0]['Attachments'] = $attachments;
                Log::info("📎 Added " . count($attachments) . " attachment(s)");
            }

            // Add CustomID for event correlation if provided
            if ($customId) {
                $payload['Messages'][0]['CustomID'] = $customId;
                Log::info("🔗 CustomID for tracking: {$customId}");
            }

            Log::debug('📨 Mailjet payload: ' . json_encode($payload));

            // Make API request with Basic Auth
            $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
                ->withoutVerifying() // For development only - remove in production
                ->post($this->apiUrl, $payload);

            Log::info("📨 Mailjet API response status: " . $response->status());

            if ($response->successful()) {
                $responseBody = $response->json();
                Log::info("✅ Mailjet API success: " . json_encode($responseBody));

                return [
                    'success' => true,
                    'response' => $responseBody,
                    'message_id' => $customId
                ];
            } else {
                $errorBody = $response->json();
                Log::error("❌ Mailjet API error: " . json_encode($errorBody));

                return [
                    'success' => false,
                    'response' => $errorBody,
                    'error' => $errorBody['ErrorMessage'] ?? 'Unknown error'
                ];
            }

        } catch (\Exception $e) {
            Log::error("❌ Exception in MailjetService::sendMessage: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate Mailjet webhook signature
     * 
     * @param string $payload Raw POST body
     * @param string $signature Signature from X-Mailjet-Signature or X-Signature header
     * @return bool True if signature is valid
     */
    public function validateWebhookSignature($payload, $signature)
    {
        try {
            // Mailjet uses HMAC-SHA256
            $computed = hash_hmac('sha256', $payload, $this->secretKey, false);
            
            // Compare with timing-safe comparison
            if (hash_equals($computed, $signature)) {
                Log::info("✅ Mailjet webhook signature validated");
                return true;
            }

            Log::warning("❌ Mailjet webhook signature validation failed");
            Log::debug("Expected: {$computed}, Got: {$signature}");
            
            return false;

        } catch (\Exception $e) {
            Log::error("❌ Exception in webhook signature validation: " . $e->getMessage());
            return false;
        }
    }
}
