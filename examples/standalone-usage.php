<?php
/**
 * Standalone PHP Usage Example
 * 
 * This example shows how to use the AdaReach SMS package
 * without Laravel in PHP 7.4+
 */

require_once __DIR__ . '/vendor/autoload.php';

use AdaReach\Sms\StandaloneClient;
use AdaReach\Sms\Exceptions\AdaReachException;

// Configuration
$username = 'your-api-username';
$password = 'your-api-password';
$sender = 'YourBrand'; // Your approved sender ID

// Initialize the client with default sender
$client = new StandaloneClient($username, $password, $sender);

// Optional: Customize base URL and cache directory
// $client = new StandaloneClient(
//     $username,
//     $password,
//     $sender,
//     'https://api.mobireach.com.bd/api',
//     '/path/to/cache'
// );

try {
    // ============================================
    // Example 1: Send SMS to a single recipient
    // ============================================
    echo "Sending SMS to single recipient...\n";
    
    // Using default sender (set in constructor)
    $response = $client->sendSms(
        '880XXXXXXXXXX',
        'Hello! This is a test message from AdaReach SMS.'
    );
    
    echo "SMS sent successfully!\n";
    echo "Message ID: " . $response['id'] . "\n";
    echo "Status: " . $response['status'] . "\n\n";
    
    // ============================================
    // Example 2: Send SMS to multiple recipients (Bulk SMS)
    // ============================================
    echo "Sending bulk SMS...\n";
    
    $recipients = [
        '880XXXXXXXXXX',
        '880YYYYYYYYYY',
        '880ZZZZZZZZZZ'
    ];
    
    // Using default sender
    $response = $client->sendSms(
        $recipients,
        'Bulk message: Special offer! Get 20% off on all items.',
        null,  // Use default sender
        'CAMPAIGN_ID_123' // Optional campaign ID
    );
    
    echo "Bulk SMS sent successfully!\n";
    echo "Total sent: " . count($response['messages']) . "\n\n";
    
    // ============================================
    // Example 3: Check account balance
    // ============================================
    echo "Checking account balance...\n";
    
    $balance = $client->checkBalance();
    
    echo "Account Balance:\n";
    echo "- Available SMS: " . $balance['balance'] . "\n";
    echo "- Currency: " . ($balance['currency'] ?? 'N/A') . "\n\n";
    
    // ============================================
    // Example 4: Get delivery status
    // ============================================
    echo "Getting delivery status...\n";
    
    $messageId = $response['id'] ?? 'MESSAGE_ID_HERE';
    $status = $client->getDeliveryStatus($messageId);
    
    echo "Delivery Status:\n";
    echo "- Message ID: " . $status['id'] . "\n";
    echo "- Status: " . $status['status'] . "\n";
    echo "- Delivered at: " . ($status['delivered_at'] ?? 'Pending') . "\n\n";
    
    // ============================================
    // Example 5: Clear token cache (if needed)
    // ============================================
    // This is useful if you want to force a new token generation
    // $client->clearCache();
    // echo "Token cache cleared.\n\n";
    
} catch (AdaReachException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
}

// ============================================
// Example 6: Using in a function
// ============================================
function sendOTP($phoneNumber, $otp)
{
    global $client, $sender;
    
    try {
        $message = "Your OTP is: {$otp}. Valid for 5 minutes.";
        $response = $client->sendSms($phoneNumber, $message, $sender);
        
        return [
            'success' => true,
            'message_id' => $response['id']
        ];
    } catch (AdaReachException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Example usage of the function
$result = sendOTP('880XXXXXXXXXX', '123456');
if ($result['success']) {
    echo "OTP sent successfully. Message ID: " . $result['message_id'] . "\n";
} else {
    echo "Failed to send OTP: " . $result['error'] . "\n";
}

// ============================================
// Example 7: Configuration from environment variables
// ============================================
/*
You can also load configuration from environment variables:

$client = new StandaloneClient(
    getenv('ADAREARCH_USERNAME') ?: 'default-username',
    getenv('ADAREARCH_PASSWORD') ?: 'default-password',
    getenv('ADAREARCH_BASE_URL') ?: 'https://api.mobireach.com.bd/api'
);
*/

// ============================================
// Example 8: Error handling with different error types
// ============================================
try {
    $response = $client->sendSms('880XXXXXXXXXX', 'Test message', $sender);
} catch (AdaReachException $e) {
    switch ($e->getCode()) {
        case 400:
            echo "Bad Request: Check your parameters\n";
            break;
        case 401:
            echo "Authentication Failed: Check your credentials\n";
            break;
        case 403:
            echo "Forbidden: You don't have permission\n";
            break;
        case 429:
            echo "Rate Limit Exceeded: Too many requests\n";
            break;
        case 500:
            echo "Server Error: Try again later\n";
            break;
        default:
            echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\nAll examples completed!\n";
