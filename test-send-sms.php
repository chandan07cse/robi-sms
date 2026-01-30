<?php
/**
 * Test SMS Send to 01703611109
 * This will test the StandaloneClient with real API credentials
 */

require_once __DIR__ . '/vendor/autoload.php';

use AdaReach\Sms\StandaloneClient;
use AdaReach\Sms\Exceptions\AdaReachException;

echo "===========================================\n";
echo "  Testing SMS Send to 01703611109\n";
echo "===========================================\n\n";

// Configuration from .env
$username = 'khulnauni';
$password = 'Khulna@1991';
$sender = '8801810187701';
$baseUrl = 'https://api.mobireach.com.bd/api';
$testPhone = '8801703611109'; // 01703611109 in international format

echo "Configuration:\n";
echo "- Username: {$username}\n";
echo "- Sender: {$sender}\n";
echo "- Base URL: {$baseUrl}\n";
echo "- Test Phone: {$testPhone}\n\n";

try {
    // Step 1: Initialize client
    echo "[1/4] Initializing client...\n";
    $client = new StandaloneClient(
        $username,
        $password,
        $sender,
        $baseUrl
    );
    echo "✓ Client initialized\n";
    echo "  - Default sender: " . $client->getSender() . "\n\n";
    
    // Step 2: Generate token
    echo "[2/4] Generating authentication token...\n";
    $tokenResponse = $client->generateToken();
    echo "✓ Token generated successfully\n";
    echo "  - Token type: " . (isset($tokenResponse['token']) ? 'Bearer' : 'Unknown') . "\n";
    echo "  - Has refresh token: " . (isset($tokenResponse['refresh_token']) ? 'Yes' : 'No') . "\n\n";
    
    // Step 3: Check balance
    echo "[3/4] Checking account balance...\n";
    $balance = $client->checkBalance();
    echo "✓ Balance retrieved\n";
    echo "  - Available SMS: " . ($balance['balance'] ?? 'Unknown') . "\n";
    if (isset($balance['currency'])) {
        echo "  - Currency: " . $balance['currency'] . "\n";
    }
    echo "\n";
    
    // Step 4: Send SMS
    echo "[4/4] Sending test SMS to {$testPhone}...\n";
    $message = "Test message from AdaReach SMS Package - " . date('Y-m-d H:i:s');
    
    $response = $client->sendSms(
        $testPhone,
        $message
    );
    
    echo "✓✓✓ SMS SENT SUCCESSFULLY! ✓✓✓\n";
    echo "  - Message ID: " . $response['id'] . "\n";
    echo "  - Status: " . $response['status'] . "\n";
    echo "  - Recipient: " . ($response['recipient'] ?? $testPhone) . "\n";
    echo "  - Message: {$message}\n";
    if (isset($response['created_at'])) {
        echo "  - Created at: " . $response['created_at'] . "\n";
    }
    echo "\n";
    
    echo "===========================================\n";
    echo "  ✓ All tests completed successfully!\n";
    echo "===========================================\n\n";
    
    echo "Check phone 01703611109 for the message!\n";
    
} catch (AdaReachException $e) {
    echo "\n✗✗✗ AdaReach API Error ✗✗✗\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n\n";
    
    // Provide specific troubleshooting
    switch ($e->getCode()) {
        case 400:
            echo "Issue: Bad Request\n";
            echo "Solution: Check phone number format (should be 8801703611109)\n";
            break;
        case 401:
            echo "Issue: Authentication Failed\n";
            echo "Solution: Verify username and password are correct\n";
            break;
        case 403:
            echo "Issue: Forbidden\n";
            echo "Solution: Your account may not have permission\n";
            break;
        case 1504:
            echo "Issue: Invalid Phone Number\n";
            echo "Solution: Check phone format (must be 13 digits: 8801703611109)\n";
            break;
        case 1505:
            echo "Issue: Invalid Sender\n";
            echo "Solution: Verify sender ID is approved\n";
            break;
        case 1506:
            echo "Issue: Insufficient Balance\n";
            echo "Solution: Recharge your account\n";
            break;
        default:
            echo "Please check the error message above\n";
    }
    
    exit(1);
    
} catch (Exception $e) {
    echo "\n✗✗✗ Unexpected Error ✗✗✗\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
    
    if (strpos($e->getMessage(), 'curl') !== false) {
        echo "This might be a network/cURL issue.\n";
        echo "Check your internet connection and firewall settings.\n";
    }
    
    exit(1);
}

echo "\nPHP Version: " . phpversion() . "\n";
echo "cURL Available: " . (function_exists('curl_init') ? 'Yes' : 'No') . "\n";
