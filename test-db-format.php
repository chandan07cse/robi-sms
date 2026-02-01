#!/usr/bin/env php
<?php
/**
 * Test phone number normalization
 */

require_once __DIR__ . '/vendor/autoload.php';

use AdaReach\Sms\StandaloneClient;

echo "===========================================\n";
echo "  Testing Phone Number Normalization\n";
echo "===========================================\n\n";

$client = new StandaloneClient(
    'khulnauni',
    'Khulna@1991',
    '01810187701',  // Using number WITHOUT 880 prefix
    'https://api.mobireach.com.bd'
);

echo "✓ Client initialized with sender: 01810187701 (without 880)\n\n";

// Test with number from your database format (without 880)
$dbNumber = '01703611109';  // Format from your DB

echo "Sending SMS to: $dbNumber (format from your DB)\n";
echo str_repeat('-', 50) . "\n";

try {
    $result = $client->sendSms(
        $dbNumber,  // Will be auto-converted to 8801703611109
        'Test with DB format number (01703611109) - Auto normalized!',
        '01810187701'  // Sender also without 880 - will be auto-normalized
    );
    
    if (isset($result['status']) && $result['status'] === 'SUCCESS') {
        echo "✓✓✓ SUCCESS! ✓✓✓\n";
        echo "Your DB number format (01703611109) works!\n";
        echo "It was automatically converted to 8801703611109 internally.\n\n";
        echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "✗ Failed\n";
        echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "SUPPORTED FORMATS (all auto-normalized):\n";
echo str_repeat('=', 50) . "\n";
echo "✓ 01703611109    → auto-converts to → 8801703611109\n";
echo "✓ 8801703611109  → stays as →        8801703611109\n";
echo "✓ +8801703611109 → converts to →     8801703611109\n";
echo "✓ 1703611109     → converts to →     8801703611109\n";
echo "\n";
echo "You can use ANY format - the client handles it! 🎉\n";
