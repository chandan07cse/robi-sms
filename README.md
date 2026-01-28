# Robi SMS - Laravel Package

A Laravel package for integrating with the Robi/AdaReach Business SMS API. Send transactional and promotional SMS messages with ease.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chandan07cse/robi-sms.svg?style=flat-square)](https://packagist.org/packages/chandan07cse/robi-sms)
[![Total Downloads](https://img.shields.io/packagist/dt/chandan07cse/robi-sms.svg?style=flat-square)](https://packagist.org/packages/chandan07cse/robi-sms)
[![License](https://img.shields.io/packagist/l/chandan07cse/robi-sms.svg?style=flat-square)](https://packagist.org/packages/chandan07cse/robi-sms)

## Features

- ✅ Send single and bulk SMS
- ✅ Check message delivery status (DLR)
- ✅ Check account balance
- ✅ Automatic token management and refresh
- ✅ Token caching for better performance
- ✅ Fluent API interface
- ✅ Automatic MSISDN formatting
- ✅ Unicode support detection
- ✅ Promotional message time validation
- ✅ Comprehensive error handling

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x

## Installation

Install the package via Composer:

```bash
composer require chandan07cse/robi-sms
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=adarearch-config
```

Add your credentials to your `.env` file:

```env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_DEFAULT_SENDER=YourSenderID
ADAREARCH_BASE_URL=https://api.mobireach.com.bd
```

## Usage

### Basic Usage - Fluent Interface

```php
use AdaReach\Sms\Facades\AdaReach;

// Send a single transactional SMS
$result = AdaReach::message()
    ->from('YourSenderID')
    ->to('01712345678')
    ->content('Your OTP is 123456')
    ->transactional()
    ->send();

// Send a promotional SMS
$result = AdaReach::message()
    ->from('YourBrand')
    ->to('01712345678')
    ->content('Special offer! Get 50% off today.')
    ->promotional()
    ->send();

// Send bulk SMS
$result = AdaReach::message()
    ->from('YourSenderID')
    ->toMany(['01712345678', '01812345678', '01912345678'])
    ->content('Bulk notification message')
    ->send();
```

### Using Default Sender

Configure `ADAREARCH_DEFAULT_SENDER` in your `.env`, then:

```php
$result = AdaReach::message()
    ->to('01712345678')
    ->content('Your message here')
    ->send();
```

### Check Message Status (DLR)

```php
$status = AdaReach::checkStatus(
    sender: 'YourSenderID',
    messageId: '626314298741755904',
    receiver: '8801712345678'
);

if ($status['status'] === 'SUCCESS') {
    echo "Message delivered successfully!";
    echo "Cost: " . $status['msgCost'];
}
```

### Check Account Balance

```php
$balance = AdaReach::checkBalance();

echo "GUI Balance: " . $balance['guiBalance'];
echo "API Balance: " . $balance['apiBalance'];
```

### Advanced Usage

#### Manual Token Management

```php
// Generate new token
$tokens = AdaReach::generateToken();

// Refresh token
$tokens = AdaReach::refreshToken();

// Clear token cache
AdaReach::clearTokenCache();
```

#### Direct API Calls

```php
$result = AdaReach::sendSms([
    'sender' => 'YourSenderID',
    'receiver' => ['8801712345678'],
    'content' => 'Your message',
    'msgType' => 'T', // T = Transactional, P = Promotional
    'requestType' => 'S', // S = Single, B = Bulk
    'contentType' => 1, // 1 = Regular, 2 = Unicode
]);
```

#### Unicode Content

The package automatically detects Unicode content:

```php
// Bengali text will automatically use contentType = 2
$result = AdaReach::message()
    ->from('YourSenderID')
    ->to('01712345678')
    ->content('আপনার OTP হল ১২৩৪৫৬')
    ->send();

// Or explicitly set content type
$result = AdaReach::message()
    ->from('YourSenderID')
    ->to('01712345678')
    ->content('Special characters: €')
    ->contentType(2)
    ->send();
```

## Response Format

### Successful SMS Send

```php
[
    'status' => 'SUCCESS',
    'description' => 'Message sent',
    'msgCost' => '0.0139',
    'currentBalance' => '49999.7915',
    'contentType' => 1,
    'msgCount' => 1,
    'errorCode' => 0,
    'messageId' => 626312999124078592
]
```

### Failed Response

```php
[
    'status' => 'FAILED',
    'description' => 'Invalid Parameter',
    'msgCost' => '0.0',
    'currentBalance' => '0',
    'contentType' => 1,
    'msgCount' => 0,
    'errorCode' => 1504,
    'messageId' => 0
]
```

## Error Handling

```php
use AdaReach\Sms\Exceptions\AdaReachException;

try {
    $result = AdaReach::message()
        ->from('YourSenderID')
        ->to('01712345678')
        ->content('Test message')
        ->send();
} catch (AdaReachException $e) {
    // Get error code
    $errorCode = $e->getCode();
    
    // Get detailed message
    $message = $e->getMessage();
    
    // Get user-friendly message
    $userMessage = $e->getUserMessage();
    
    Log::error('SMS sending failed', [
        'code' => $errorCode,
        'message' => $message
    ]);
}
```

### Common Error Codes

| Code | Description |
|------|-------------|
| 1501 | Invalid Token |
| 1502 | TPS Limit Exceeded |
| 1503 | Sender Not Allowed |
| 1504 | Invalid Parameter |
| 1505 | Missing Parameter |
| 1506 | Insufficient Balance |
| 1513 | Wrong Content Type |
| 1514 | MSISDN Limit Exceeded (max 400) |

## MSISDN Formatting

The package automatically formats phone numbers:

```php
// All these formats work:
'01712345678'    → '8801712345678'
'1712345678'     → '8801712345678'
'8801712345678'  → '8801712345678' (unchanged)
'+8801712345678' → '8801712345678' (+ removed)
```

## Important Notes

### Promotional Messages
- Must be sent between **9 AM - 8 PM** (Bangladesh time)
- Must use `contentType = 2` (Unicode)
- Use `->promotional()` method for automatic configuration

### Token Management
- Tokens are valid for **1 hour**
- Tokens are automatically cached for **55 minutes**
- Refresh tokens before expiry for seamless service
- Use `refreshToken()` instead of repeatedly generating new tokens

### Bulk SMS
- Maximum **400 MSISDNs** per request
- For more recipients, split into multiple requests

## Testing

```php
// In your tests
use AdaReach\Sms\Facades\AdaReach;
use Illuminate\Support\Facades\Http;

public function test_can_send_sms()
{
    Http::fake([
        '*/auth/tokens' => Http::response([
            'token' => 'fake-token',
            'refresh_token' => 'fake-refresh-token'
        ]),
        '*/sms/send' => Http::response([
            'status' => 'SUCCESS',
            'messageId' => 123456789,
        ]),
    ]);

    $result = AdaReach::message()
        ->from('TestSender')
        ->to('01712345678')
        ->content('Test message')
        ->send();

    $this->assertEquals('SUCCESS', $result['status']);
}
```

## Support

For any queries or assistance, contact: helpdesk@adaglobal.com

## Credits

- [chandan07cse](https://github.com/chandan07cse)
- Built for Robi/AdaReach Business SMS API

## License

MIT License

---

**Note:** Always test thoroughly in a development environment before using in production.
