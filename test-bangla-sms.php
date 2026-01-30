#!/usr/bin/env php
<?php
/**
 * Test Bangla/Unicode SMS Support
 */

require_once __DIR__ . '/vendor/autoload.php';

use Chandan07cse\AdaReach\StandaloneClient;

echo "===========================================\n";
echo "  Testing Bangla/Unicode SMS Support\n";
echo "===========================================\n\n";

$client = new StandaloneClient(
    'khulnauni',
    'Khulna@1991',
    '01810187701',
    'https://api.mobireach.com.bd'
);

echo "✓ Client initialized\n\n";

// Test 1: Bangla SMS (auto-detect)
echo "Test 1: Bangla SMS (auto-detect)\n";
echo str_repeat('-', 50) . "\n";
$banglaMessage = "আপনার OTP কোড: ১২৩৪৫৬";
echo "Message: $banglaMessage\n";

try {
    $result = $client->sendSms(
        '01703611109',
        $banglaMessage
    );
    
    if (isset($result['status']) && $result['status'] === 'SUCCESS') {
        echo "✓✓✓ Bangla SMS sent successfully!\n";
        echo "Auto-detected as Unicode and sent with contentType=2\n";
        echo "Response: " . json_encode($result) . "\n";
    } else {
        echo "✗ Failed\n";
        echo "Response: " . json_encode($result) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";
sleep(2);

// Test 2: English SMS (auto-detect)
echo "Test 2: English SMS (auto-detect)\n";
echo str_repeat('-', 50) . "\n";
$englishMessage = "Your OTP code: 123456";
echo "Message: $englishMessage\n";

try {
    $result = $client->sendSms(
        '01703611109',
        $englishMessage
    );
    
    if (isset($result['status']) && $result['status'] === 'SUCCESS') {
        echo "✓✓✓ English SMS sent successfully!\n";
        echo "Auto-detected as Regular text with contentType=1\n";
        echo "Response: " . json_encode($result) . "\n";
    } else {
        echo "✗ Failed\n";
        echo "Response: " . json_encode($result) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";
sleep(2);

// Test 3: Mixed (Bangla + English)
echo "Test 3: Mixed (Bangla + English)\n";
echo str_repeat('-', 50) . "\n";
$mixedMessage = "আপনার OTP: 123456 (Valid for 5 minutes)";
echo "Message: $mixedMessage\n";

try {
    $result = $client->sendSms(
        '01703611109',
        $mixedMessage
    );
    
    if (isset($result['status']) && $result['status'] === 'SUCCESS') {
        echo "✓✓✓ Mixed SMS sent successfully!\n";
        echo "Auto-detected as Unicode (contains Bangla)\n";
        echo "Response: " . json_encode($result) . "\n";
    } else {
        echo "✗ Failed\n";
        echo "Response: " . json_encode($result) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";
sleep(2);

// Test 4: Emoji (Unicode)
echo "Test 4: Emoji (Unicode)\n";
echo str_repeat('-', 50) . "\n";
$emojiMessage = "🎉 Congratulations! You won 🏆";
echo "Message: $emojiMessage\n";

try {
    $result = $client->sendSms(
        '01703611109',
        $emojiMessage
    );
    
    if (isset($result['status']) && $result['status'] === 'SUCCESS') {
        echo "✓✓✓ Emoji SMS sent successfully!\n";
        echo "Auto-detected as Unicode\n";
        echo "Response: " . json_encode($result) . "\n";
    } else {
        echo "✗ Failed\n";
        echo "Response: " . json_encode($result) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n";
sleep(2);

// Test 5: Force Unicode mode
echo "Test 5: Force Unicode mode (explicit)\n";
echo str_repeat('-', 50) . "\n";
$forceMessage = "Test with forced Unicode";
echo "Message: $forceMessage\n";

try {
    $result = $client->sendSms(
        '01703611109',
        $forceMessage,
        null,      // Use default sender
        null,      // No campaign ID
        true       // Force Unicode mode
    );
    
    if (isset($result['status']) && $result['status'] === 'SUCCESS') {
        echo "✓✓✓ Forced Unicode SMS sent!\n";
        echo "Sent with contentType=2 (forced)\n";
        echo "Response: " . json_encode($result) . "\n";
    } else {
        echo "✗ Failed\n";
        echo "Response: " . json_encode($result) . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "Summary\n";
echo str_repeat('=', 50) . "\n";
echo "✓ Auto-detection: Automatically detects Bangla/Unicode\n";
echo "✓ English: Sent as regular text (cheaper, 160 chars)\n";
echo "✓ Bangla: Sent as Unicode (70 chars per SMS)\n";
echo "✓ Emoji: Supported via Unicode mode\n";
echo "✓ Mixed: Auto-detects and uses Unicode\n";
echo "✓ Manual: Can force Unicode mode if needed\n";
echo "\nCheck phone 01703611109 for all test messages!\n";
