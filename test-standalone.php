<?php
/**
 * Quick Test Script for Standalone Client
 * 
 * Usage: php test-standalone.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use AdaReach\Sms\StandaloneClient;
use AdaReach\Sms\Exceptions\AdaReachException;

echo "===========================================\n";
echo "  AdaReach SMS - Standalone Client Test\n";
echo "===========================================\n\n";

// Configuration - Update these with your credentials
$username = 'your-api-username';
$password = 'your-api-password';
$baseUrl = 'https://api.mobireach.com.bd/api';
$testPhone = '880XXXXXXXXXX'; // Your test phone number
$sender = 'YourBrand'; // Your approved sender ID

echo "Configuration:\n";
echo "- Base URL: {$baseUrl}\n";
echo "- Username: {$username}\n";
echo "- Test Phone: {$testPhone}\n";
echo "- Sender: {$sender}\n\n";

try {
    // Initialize client
    echo "[1/3] Initializing client...\n";
    $client = new StandaloneClient($username, $password, $baseUrl);
    echo "✓ Client initialized successfully\n\n";
    
    // Test 1: Check balance
    echo "[2/3] Checking account balance...\n";
    $balance = $client->checkBalance();
    echo "✓ Balance check successful\n";
    echo "  - Available SMS: " . $balance['balance'] . "\n";
    if (isset($balance['currency'])) {
        echo "  - Currency: " . $balance['currency'] . "\n";
    }
    echo "\n";
    
    // Test 2: Send test SMS
    echo "[3/3] Sending test SMS...\n";
    echo "  Note: Uncomment the line below to actually send SMS\n";
    
    // Uncomment to send actual SMS:
    /*
    $response = $client->sendSms(
        $testPhone,
        'Test message from AdaReach SMS Standalone Client - ' . date('Y-m-d H:i:s'),
        $sender
    );
    echo "✓ SMS sent successfully\n";
    echo "  - Message ID: " . $response['id'] . "\n";
    echo "  - Status: " . $response['status'] . "\n";
    */
    
    echo "  (Skipped to avoid sending real SMS)\n\n";
    
    echo "===========================================\n";
    echo "  ✓ All tests completed successfully!\n";
    echo "===========================================\n\n";
    
    echo "PHP Version: " . phpversion() . "\n";
    echo "PHP 7.4+ Compatible: " . (version_compare(phpversion(), '7.4.0', '>=') ? 'YES' : 'NO') . "\n";
    
} catch (AdaReachException $e) {
    echo "\n✗ AdaReach API Error:\n";
    echo "  Message: " . $e->getMessage() . "\n";
    echo "  Code: " . $e->getCode() . "\n\n";
    
    // Provide helpful suggestions
    switch ($e->getCode()) {
        case 401:
            echo "Suggestion: Check your username and password are correct\n";
            break;
        case 403:
            echo "Suggestion: Your account may not have permission for this action\n";
            break;
        case 1506:
            echo "Suggestion: Insufficient balance. Please recharge your account\n";
            break;
        default:
            echo "Suggestion: Review the error message and API documentation\n";
    }
    
    exit(1);
    
} catch (Exception $e) {
    echo "\n✗ Unexpected Error:\n";
    echo "  " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\nTo send actual SMS, edit this file and:\n";
echo "1. Update the configuration variables at the top\n";
echo "2. Uncomment the sendSms() call in Test 2\n";
echo "3. Run: php test-standalone.php\n";
