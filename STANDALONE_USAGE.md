# AdaReach SMS - Standalone PHP Usage (PHP 7.4+)

This guide shows how to use the AdaReach SMS package **without Laravel** in any PHP 7.4+ project.

## Requirements

- PHP 7.4 or higher
- cURL extension
- JSON extension
- Composer

## Installation

Install via Composer:

```bash
composer require chandan07cse/robi-sms
```

## Quick Start

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use AdaReach\Sms\StandaloneClient;

// Initialize client
$client = new StandaloneClient(
    'your-api-username',
    'your-api-password',
    'https://api.mobireach.com.bd/api'
);

// Send SMS
try {
    $response = $client->sendSms(
        '880XXXXXXXXXX',
        'Hello! This is a test message.',
        'YourBrand'
    );
    
    echo "SMS sent! Message ID: " . $response['id'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Features

✅ **PHP 7.4 Compatible** - Works with PHP 7.4, 8.0, 8.1, 8.2, 8.3  
✅ **No Laravel Required** - Pure PHP implementation  
✅ **cURL Based** - No external HTTP client dependencies  
✅ **Token Caching** - Automatic token management with file-based caching  
✅ **Single & Bulk SMS** - Send to one or multiple recipients  
✅ **Balance Check** - Check your account balance  
✅ **Delivery Status** - Track SMS delivery status  
✅ **Error Handling** - Comprehensive exception handling  

## API Reference

### Initialize Client

```php
$client = new StandaloneClient(
    string $username,
    string $password,
    string $baseUrl = 'https://api.mobireach.com.bd/api',
    string $cacheDir = null  // Optional: Custom cache directory for tokens
);
```

### Send Single SMS

```php
$response = $client->sendSms(
    string $recipient,      // Phone number (e.g., '880XXXXXXXXXX')
    string $message,        // SMS text
    string $sender = null,  // Optional: Sender ID
    string $campaignId = null  // Optional: Campaign ID
);
```

**Response:**
```php
[
    'id' => 'message-uuid',
    'status' => 'queued',
    'recipient' => '880XXXXXXXXXX',
    'created_at' => '2026-01-31T10:30:00Z'
]
```

### Send Bulk SMS

```php
$recipients = [
    '880XXXXXXXXXX',
    '880YYYYYYYYYY',
    '880ZZZZZZZZZZ'
];

$response = $client->sendSms(
    array $recipients,
    string $message,
    string $sender = null,
    string $campaignId = null
);
```

**Response:**
```php
[
    'messages' => [
        ['id' => 'uuid-1', 'recipient' => '880XXXXXXXXXX', 'status' => 'queued'],
        ['id' => 'uuid-2', 'recipient' => '880YYYYYYYYYY', 'status' => 'queued'],
        ['id' => 'uuid-3', 'recipient' => '880ZZZZZZZZZZ', 'status' => 'queued']
    ],
    'total' => 3,
    'created_at' => '2026-01-31T10:30:00Z'
]
```

### Check Balance

```php
$balance = $client->checkBalance();
```

**Response:**
```php
[
    'balance' => 1000,
    'currency' => 'BDT',
    'updated_at' => '2026-01-31T10:30:00Z'
]
```

### Get Delivery Status

```php
$status = $client->getDeliveryStatus($messageId);
```

**Response:**
```php
[
    'id' => 'message-uuid',
    'status' => 'delivered',  // queued, sent, delivered, failed
    'recipient' => '880XXXXXXXXXX',
    'delivered_at' => '2026-01-31T10:35:00Z',
    'error' => null
]
```

### Clear Token Cache

```php
$client->clearCache();
```

Clears cached authentication tokens. Useful when you want to force a new token generation.

## Error Handling

All API methods throw `AdaReach\Sms\Exceptions\AdaReachException` on errors:

```php
use AdaReach\Sms\Exceptions\AdaReachException;

try {
    $response = $client->sendSms('880XXXXXXXXXX', 'Test message', 'Sender');
} catch (AdaReachException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    
    // Handle specific error codes
    switch ($e->getCode()) {
        case 400:
            // Bad Request - Invalid parameters
            break;
        case 401:
            // Unauthorized - Invalid credentials
            break;
        case 403:
            // Forbidden - Permission denied
            break;
        case 429:
            // Too Many Requests - Rate limit exceeded
            break;
        case 500:
            // Server Error
            break;
    }
}
```

## Common Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Check your parameters |
| 401 | Authentication Failed - Check credentials |
| 403 | Forbidden - Permission denied |
| 429 | Rate Limit Exceeded |
| 500 | Server Error |

## Configuration Options

### Using Environment Variables

```php
// Load from environment variables
$client = new StandaloneClient(
    getenv('ADAREARCH_USERNAME'),
    getenv('ADAREARCH_PASSWORD'),
    getenv('ADAREARCH_BASE_URL') ?: 'https://api.mobireach.com.bd/api'
);
```

### Custom Cache Directory

By default, tokens are cached in the system temp directory. You can specify a custom location:

```php
$client = new StandaloneClient(
    'username',
    'password',
    'https://api.mobireach.com.bd/api',
    '/var/www/cache/sms-tokens'  // Custom cache directory
);
```

**Requirements for cache directory:**
- Must be writable
- Should be persistent across requests
- Should not be publicly accessible

## Real-World Examples

### OTP/Verification Code

```php
function sendVerificationCode($phoneNumber)
{
    $otp = rand(100000, 999999);
    $message = "Your verification code is: {$otp}. Valid for 5 minutes.";
    
    global $client;
    $response = $client->sendSms($phoneNumber, $message, 'YourApp');
    
    // Store OTP in database/session with expiry
    // Return message ID for tracking
    return [
        'otp' => $otp,
        'message_id' => $response['id']
    ];
}
```

### Order Notifications

```php
function sendOrderConfirmation($orderDetails)
{
    $message = "Order #{$orderDetails['id']} confirmed! "
             . "Total: {$orderDetails['amount']} BDT. "
             . "Expected delivery: {$orderDetails['delivery_date']}";
    
    global $client;
    return $client->sendSms(
        $orderDetails['phone'],
        $message,
        'YourStore'
    );
}
```

### Bulk Campaign

```php
function sendPromotionalCampaign($customerList, $campaignMessage)
{
    $phoneNumbers = array_column($customerList, 'phone');
    
    global $client;
    $response = $client->sendSms(
        $phoneNumbers,
        $campaignMessage,
        'YourBrand',
        'PROMO_' . date('Ymd')
    );
    
    return [
        'total_sent' => count($response['messages']),
        'campaign_id' => 'PROMO_' . date('Ymd')
    ];
}
```

## Best Practices

1. **Cache Directory**: Ensure the cache directory is writable and persistent
2. **Error Handling**: Always wrap API calls in try-catch blocks
3. **Phone Format**: Use international format (880XXXXXXXXXX)
4. **Message Length**: Keep messages under 160 characters for single SMS
5. **Rate Limiting**: Implement your own rate limiting for bulk sending
6. **Token Management**: Don't clear cache unnecessarily; tokens are auto-refreshed
7. **Security**: Never expose credentials in public repositories

## Token Management

The client automatically handles token generation, caching, and refresh:

1. **First Request**: Generates a new token and caches it
2. **Subsequent Requests**: Uses cached token
3. **Token Expiry**: Automatically refreshes when expired
4. **Cache Location**: `{temp_dir}/adarearch_{username_hash}.json`

No manual token management needed!

## Testing

Test your integration with a simple script:

```php
<?php

require_once 'vendor/autoload.php';

use AdaReach\Sms\StandaloneClient;

$client = new StandaloneClient('username', 'password');

// Test 1: Check credentials
try {
    echo "Testing credentials...\n";
    $balance = $client->checkBalance();
    echo "✓ Credentials valid. Balance: " . $balance['balance'] . "\n\n";
} catch (Exception $e) {
    echo "✗ Credentials invalid: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Send test SMS
try {
    echo "Sending test SMS...\n";
    $response = $client->sendSms(
        '880XXXXXXXXXX',
        'Test message from AdaReach SMS',
        'TestApp'
    );
    echo "✓ SMS sent. Message ID: " . $response['id'] . "\n";
} catch (Exception $e) {
    echo "✗ Failed to send SMS: " . $e->getMessage() . "\n";
}
```

## Troubleshooting

### cURL Error

```
Error: Could not resolve host
```

**Solution**: Check internet connectivity and firewall settings

### Authentication Failed

```
Error: Authentication Failed (Code: 401)
```

**Solution**: Verify your username and password are correct

### Permission Denied

```
Error: Forbidden (Code: 403)
```

**Solution**: Your account may not have permission for this action. Contact support.

### Cache Permission Error

```
Error: Failed to write to cache file
```

**Solution**: Ensure the cache directory is writable:
```bash
chmod 755 /path/to/cache
```

## Comparison with Laravel Integration

| Feature | Standalone | Laravel Integration |
|---------|-----------|-------------------|
| PHP Version | 7.4+ | 8.1+ (for Laravel 10+) |
| Dependencies | cURL only | Laravel framework |
| Setup | Instantiate class | Facade/DI container |
| Configuration | Constructor | Config files |
| Caching | File-based | Laravel Cache |
| Dashboard | Not available | Full web dashboard |
| Queue Support | Manual | Laravel Queue |
| Events | Manual | Laravel Events |

## Support

For issues, questions, or contributions:
- GitHub: https://github.com/chandan07cse/robi-sms
- Email: chandan07cse@gmail.com

## License

MIT License - see LICENSE file for details
