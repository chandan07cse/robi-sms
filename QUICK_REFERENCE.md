# AdaReach SMS - Quick Reference Card

## Installation

```bash
composer require chandan07cse/robi-sms
```

## Two Usage Modes

### 1️⃣ Laravel Integration (PHP 8.1+)

Full-featured package with dashboard, queue, events.

```php
use AdaReach\Sms\Facades\AdaReach;

// Send SMS
AdaReach::sendSingle('880XXXXXXXXXX', 'Hello!');
AdaReach::sendBulk(['880XXX...', '880YYY...'], 'Message');

// Check balance
$balance = AdaReach::checkBalance();
```

**Setup:**
1. `composer require chandan07cse/robi-sms`
2. `php artisan vendor:publish --provider="AdaReach\Sms\AdaReachServiceProvider"`
3. `php artisan migrate`
4. Configure `.env`
5. Visit `/sms-dashboard`

### 2️⃣ Standalone Mode (PHP 7.4+)

No Laravel required. Works in any PHP project.

```php
use AdaReach\Sms\StandaloneClient;

$client = new StandaloneClient('username', 'password');

// Send SMS
$client->sendSms('880XXXXXXXXXX', 'Hello!', 'Sender');
$client->sendSms(['880XXX...'], 'Bulk', 'Sender');

// Check balance
$balance = $client->checkBalance();

// Get status
$status = $client->getDeliveryStatus($messageId);

// Clear cache
$client->clearCache();
```

## Quick Examples

### Single SMS
```php
// Laravel
AdaReach::sendSingle('880XXXXXXXXXX', 'Your OTP: 123456');

// Standalone
$client->sendSms('880XXXXXXXXXX', 'Your OTP: 123456', 'YourApp');
```

### Bulk SMS
```php
$recipients = ['880XXXXXXXXXX', '880YYYYYYYYYY'];

// Laravel
AdaReach::sendBulk($recipients, 'Sale! 50% off today!');

// Standalone
$client->sendSms($recipients, 'Sale! 50% off today!', 'YourStore');
```

### Check Balance
```php
// Laravel
$balance = AdaReach::checkBalance();
echo "Balance: " . $balance['balance'];

// Standalone
$balance = $client->checkBalance();
echo "Balance: " . $balance['balance'];
```

### Error Handling
```php
use AdaReach\Sms\Exceptions\AdaReachException;

try {
    // Your code here
} catch (AdaReachException $e) {
    echo "Error: " . $e->getMessage();
    echo "Code: " . $e->getCode();
}
```

## Common Error Codes

| Code | Meaning | Solution |
|------|---------|----------|
| 400 | Bad Request | Check parameters |
| 401 | Auth Failed | Verify credentials |
| 403 | Forbidden | Check permissions |
| 429 | Rate Limit | Slow down requests |
| 500 | Server Error | Try again later |
| 1504 | Invalid Number | Check phone format |
| 1505 | Invalid Sender | Verify sender ID |
| 1506 | No Balance | Recharge account |

## Phone Number Format

✅ Correct: `880XXXXXXXXXX` (13 digits)  
❌ Wrong: `01XXXXXXXXX`, `+8801XXX`, `8801XXX`

## Configuration

### Laravel (.env)
```env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_SENDER=YourBrand
ADAREARCH_BASE_URL=https://api.mobireach.com.bd/api

# Dashboard auth
ADAREARCH_AUTH_ENABLED=true
ADAREARCH_DASHBOARD_USERNAME=admin
ADAREARCH_DASHBOARD_PASSWORD=hashed_password
```

### Standalone
```php
$client = new StandaloneClient(
    'username',          // API username
    'password',          // API password
    'https://api.mobireach.com.bd/api',  // Base URL
    '/custom/cache/dir'  // Optional: cache directory
);
```

## API Methods

### Laravel Facade
```php
AdaReach::sendSingle($receiver, $message, $sender = null)
AdaReach::sendBulk($receivers, $message, $sender = null)
AdaReach::checkBalance()
AdaReach::getDeliveryStatus($messageId)
```

### Standalone Client
```php
$client->sendSms($recipients, $message, $sender = null, $campaignId = null)
$client->checkBalance()
$client->getDeliveryStatus($messageId)
$client->generateToken()
$client->refreshToken()
$client->clearCache()
```

## Response Format

### Send SMS (Single)
```php
[
    'id' => 'uuid',
    'status' => 'queued',
    'recipient' => '880XXXXXXXXXX',
    'created_at' => '2026-01-31T10:30:00Z'
]
```

### Send SMS (Bulk)
```php
[
    'messages' => [
        ['id' => 'uuid1', 'recipient' => '880XXX...', 'status' => 'queued'],
        ['id' => 'uuid2', 'recipient' => '880YYY...', 'status' => 'queued']
    ],
    'total' => 2
]
```

### Check Balance
```php
[
    'balance' => 1000,
    'currency' => 'BDT'
]
```

### Delivery Status
```php
[
    'id' => 'uuid',
    'status' => 'delivered',  // queued, sent, delivered, failed
    'recipient' => '880XXXXXXXXXX',
    'delivered_at' => '2026-01-31T10:35:00Z'
]
```

## Requirements

### Laravel Integration
- PHP 8.1+
- Laravel 10.x or 11.x
- MySQL/PostgreSQL
- Redis (optional)
- Node.js 16+ (for dashboard)

### Standalone
- PHP 7.4+
- cURL extension
- JSON extension
- Composer

## Documentation

- **Main README:** [README.md](README.md)
- **Standalone Guide:** [STANDALONE_USAGE.md](STANDALONE_USAGE.md)
- **Examples:** [examples/standalone-usage.php](examples/standalone-usage.php)
- **Test Script:** [test-standalone.php](test-standalone.php)
- **Changelog:** [CHANGELOG.md](CHANGELOG.md)
- **Implementation:** [PHP74_STANDALONE_IMPLEMENTATION.md](PHP74_STANDALONE_IMPLEMENTATION.md)

## Testing

### Laravel
```bash
# Run dashboard
php artisan serve
# Visit: http://localhost:8000/sms-dashboard

# Run tests
./vendor/bin/phpunit
```

### Standalone
```bash
# Quick test
php test-standalone.php

# Run examples
php examples/standalone-usage.php
```

## Tips

💡 **Token Management:** Tokens are cached automatically. No manual refresh needed.

💡 **Sender ID:** If not specified, uses default from config/database.

💡 **Message Length:** Keep under 160 characters for single SMS.

💡 **Bulk SMS:** Can send to unlimited recipients in one call.

💡 **Cache Directory:** Must be writable and persistent (standalone).

💡 **Error Handling:** Always wrap API calls in try-catch blocks.

## Support

- **GitHub:** https://github.com/chandan07cse/robi-sms
- **Email:** chandan07cse@gmail.com
- **Issues:** https://github.com/chandan07cse/robi-sms/issues

## License

MIT License
