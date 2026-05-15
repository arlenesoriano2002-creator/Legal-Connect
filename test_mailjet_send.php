<?php
/**
 * Test Mailjet API directly
 * Run from project root: php test_mailjet_send.php
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "=== Testing Mailjet API Send ===\n\n";

// Initialize Laravel
$app = require_once('bootstrap/app.php');
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Test credentials
    $apiKey = 'e0fa4001b29ab0df49a2fbab6c9b3dac';
    $secretKey = '7bdd0503cab0849ad5647a568da38b02';
    $apiUrl = 'https://api.mailjet.com/v3.1/send';
    
    echo "Test 1: Credentials loaded\n";
    echo "  API Key: " . substr($apiKey, 0, 8) . "...\n";
    echo "  Secret Key: " . substr($secretKey, 0, 8) . "...\n";
    echo "  API URL: " . $apiUrl . "\n\n";
    
    echo "Test 2: Building payload\n";
    
    $payload = [
        'Messages' => [
            [
                'From' => [
                    'Email' => 'legalconnect681@gmail.com',
                    'Name' => 'LegalConnect'
                ],
                'To' => [
                    [
                        'Email' => 'jeromecafirma.itspecialist@gmail.com',
                        'Name' => 'Test User'
                    ]
                ],
                'Subject' => 'Test Email from Mailjet API',
                'TextPart' => 'This is a test email to verify Mailjet API integration',
                'HTMLPart' => '<h1>Test Email</h1><p>This is a test email to verify Mailjet API integration</p>',
                'CustomID' => 'test-' . time()
            ]
        ]
    ];
    
    echo "  ✅ Payload built successfully\n";
    echo "  From: " . $payload['Messages'][0]['From']['Email'] . "\n";
    echo "  To: " . $payload['Messages'][0]['To'][0]['Email'] . "\n";
    echo "  Subject: " . $payload['Messages'][0]['Subject'] . "\n\n";
    
    echo "Test 3: Making API request\n";
    
    $response = Http::withBasicAuth($apiKey, $secretKey)
        ->withoutVerifying()
        ->post($apiUrl, $payload);
    
    echo "  Status: " . $response->status() . "\n";
    echo "  Success: " . ($response->successful() ? "YES" : "NO") . "\n\n";
    
    echo "Test 4: Response details\n";
    $responseBody = $response->json();
    echo json_encode($responseBody, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($response->successful()) {
        echo "✅ TEST PASSED - Email API call successful!\n";
        if (isset($responseBody['Messages']) && count($responseBody['Messages']) > 0) {
            echo "✅ Message accepted by Mailjet\n";
            if (isset($responseBody['Messages'][0]['Status'])) {
                echo "   Status: " . $responseBody['Messages'][0]['Status'] . "\n";
            }
        }
    } else {
        echo "❌ TEST FAILED - API returned error\n";
        if (isset($responseBody['ErrorMessage'])) {
            echo "   Error: " . $responseBody['ErrorMessage'] . "\n";
        }
        if (isset($responseBody['Errors'])) {
            echo "   Errors: " . json_encode($responseBody['Errors']) . "\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
    exit(1);
}
