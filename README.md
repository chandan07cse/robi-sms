# AdaReach SMS - Laravel Package & Standalone Library

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chandan07cse/robi-sms.svg?style=flat-square)](https://packagist.org/packages/chandan07cse/robi-sms)
[![Total Downloads](https://img.shields.io/packagist/dt/chandan07cse/robi-sms.svg?style=flat-square)](https://packagist.org/packages/chandan07cse/robi-sms)
[![License](https://img.shields.io/packagist/l/chandan07cse/robi-sms.svg?style=flat-square)](https://packagist.org/packages/chandan07cse/robi-sms)
[![PHP Version](https://img.shields.io/packagist/php-v/chandan07cse/robi-sms.svg?style=flat-square)](https://packagist.org/packages/chandan07cse/robi-sms)

A comprehensive package for integrating with AdaReach (Robi/MobiReach) Business SMS API. Works with **Laravel** or as a **standalone PHP library** (PHP 7.4+).

## ⚡ Quick Start

### Installation

```bash
composer require chandan07cse/robi-sms
```

### Dashboard Access (1 Line Setup)

Add to your `index.php` or main routing file:

```php
require __DIR__ . '/vendor/chandan07cse/robi-sms/routes/sms-dashboard.php';
```

✅ **Done!** Visit: `http://yoursite.com/sms-dashboard`

### Send SMS (Standalone PHP)

```php
use AdaReach\Sms\StandaloneClient;

$client = new StandaloneClient('username', 'password', 'SenderID');

// Phone auto-normalized (01XXX → 880XXX)
$response = $client->sendSms('01703611094', 'Hello!');

// Bangla SMS (Unicode auto-detected)
$response = $client->sendSms('01703611094', 'হ্যালো বাংলা!');

// Check balance
$balance = $client->getBalance();
```

---

## 📦 Two Ways to Use

### 1. Laravel Integration
Full-featured Laravel package with dashboard, queue support, and events.
- [Laravel Installation Guide →](INSTALLATION.md)

### 2. Standalone PHP Library (No Laravel)
Use in any PHP 7.4+ project without Laravel dependencies.
- [Standalone Documentation →](STANDALONE_USAGE.md)
- [Quick Setup Guide →](QUICK_SETUP.md)

---

## Features

✨ **Core Features:**
- Send single and bulk SMS messages
- **Phone auto-normalization** (01XXX → 880XXX)
- **Bangla/Unicode auto-detection**
- Real-time SMS delivery tracking
- Beautiful dashboard interface
- Balance checking (API & GUI balance)
- **PHP 7.4+ compatible**
- **Works without Laravel** (Standalone mode)

🎨 **Dashboard Features:**
- Modern, responsive UI with tabs
- Character counter with SMS parts
- Single & Bulk SMS sending
- Real-time balance display
- Credential management

🔒 **Security:**
- Token-based authentication with auto-refresh
- Encrypted credentials (Laravel mode)
- File-based token caching (Standalone mode)
- Database-backed configuration (Laravel)
- File-based token caching (Standalone)

## Requirements

### For Laravel Integration
- PHP 8.1 or higher
- Laravel 10.x or 11.x
- MySQL/PostgreSQL database
- Redis (optional, for caching)
- Node.js 16+ (for dashboard assets)

### For Standalone Usage
- PHP 7.4 or higher
- cURL extension
- JSON extension
- Composer

## Installation

### Option 1: Laravel Integration

#### Step 1: Install via Composer

```bash
composer require chandan07cse/robi-sms
```

### Step 2: Publish Configuration and Assets

```bash
# Publish configuration file
php artisan vendor:publish --tag=adarearch-config

# Publish migration files
php artisan vendor:publish --tag=adarearch-migrations

# Publish dashboard assets (JS, CSS)
php artisan vendor:publish --tag=adarearch-assets

# Publish all at once
php artisan vendor:publish --provider="AdaReach\Sms\AdaReachServiceProvider"
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

This will create:
- `adarearch_sms` - SMS records table
- `adarearch_settings` - Settings storage table

### Step 4: Configure API Credentials

You can configure credentials in two ways:

#### Option A: Environment Variables (`.env`)

```env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_SENDER=880XXXXXXXXXX
ADAREARCH_BASE_URL=https://api.mobireach.com.bd/api

# Dashboard Authentication (optional, enabled by default)
ADAREARCH_AUTH_ENABLED=true
ADAREARCH_DASHBOARD_USERNAME=admin
ADAREARCH_DASHBOARD_PASSWORD=hashed_password_here
```

**Generate a hashed password:**
```bash
php artisan adarearch:password yourpassword
```

This will output a hashed password that you can add to your `.env` file.

#### Option B: Dashboard Settings (Recommended)

1. Access the dashboard at `/adarearch`
2. Navigate to Settings
3. Enter your API credentials
4. Click "Save Settings"

Credentials will be encrypted and stored in the database.

### Step 5: Add Route (Optional)

The package automatically registers the `/adarearch` route. If you want to customize it, publish the routes:

```bash
php artisan vendor:publish --tag=adarearch-routes
```

## Usage

### Dashboard Authentication

The dashboard is password-protected by default. To access it:

1. Navigate to `http://your-app.test/sms-dashboard/login`
2. Enter your credentials (default username: `admin`)
3. You'll be redirected to the dashboard

**Disable Authentication (Not Recommended for Production):**

In your `.env` file:
```env
ADAREARCH_AUTH_ENABLED=false
```

**Change Login Credentials:**

1. Generate a new password hash:
```bash
php artisan adarearch:password your_new_password
```

2. Update your `.env`:
```env
ADAREARCH_DASHBOARD_USERNAME=your_username
ADAREARCH_DASHBOARD_PASSWORD=hashed_password_from_command
```

### Using the Dashboard

Access the dashboard at: `http://your-app.test/adarearch`

The dashboard provides:
- **Dashboard**: Overview with today's statistics
- **Send SMS**: Quick send interface with cost estimation
- **SMS Messages**: History of all sent messages
- **Analytics**: Charts and statistics
- **Settings**: API configuration and preferences

### Programmatic Usage (Without Dashboard)

**Note:** The `sender` parameter is optional in all methods. If not provided, it will automatically use the default sender from:
1. Database settings (if configured via dashboard)
2. Config file (`config/adarearch.php`)
3. Environment variable (`ADAREARCH_SENDER`)

#### 1. Using the Facade

```php
use AdaReach\Sms\Facades\AdaReach;

// Send single SMS (sender loaded from config/database)
$result = AdaReach::sendSingle(
    receiver: '01712345678',
    message: 'Hello from AdaReach SMS!'
);

// Or specify a custom sender
$result = AdaReach::sendSingle(
    receiver: '01712345678',
    message: 'Hello from AdaReach SMS!',
    sender: '880XXXXXXXXXX'  // Optional: override default sender
);

// Send bulk SMS (sender loaded from config/database)
$result = AdaReach::sendBulk(
    receivers: ['01712345678', '01812345678'],
    message: 'Bulk message to all recipients'
);

// Check balance
$balance = AdaReach::checkBalance();
echo "GUI Balance: {$balance['guiBalance']} BDT\n";
echo "API Balance: {$balance['apiBalance']} BDT\n";

// Check message status
$status = AdaReach::checkStatus('message_request_id');
```

#### 2. Using the Client Directly

```php
use AdaReach\Sms\AdaReachClient;

$client = new AdaReachClient(
    username: 'your_username',
    password: 'your_password',
    baseUrl: 'https://api.mobireach.com.bd/api'
);

// Generate token
$token = $client->generateToken();

// Send SMS
$result = $client->sendSms([
    'sender' => '880XXXXXXXXXX',
    'receiver' => ['01712345678'],
    'content' => 'Your message here',
    'msgType' => 1,
    'requestType' => 1,
    'contentType' => 1
]);
```

#### 3. Using the SMS Builder

```php
use AdaReach\Sms\SmsBuilder;

$sms = new SmsBuilder();

// Send with default sender from config/database
$result = $sms->to('01712345678')
    ->message('Hello from SMS Builder!')
    ->send();

// Or specify a custom sender
$result = $sms->sender('880XXXXXXXXXX')
    ->to('01712345678')
    ->message('Hello with custom sender!')
    ->send();

// Send to multiple recipients
$result = $sms->to(['01712345678', '01812345678'])
    ->message('Bulk message via builder')
    ->send();
```

#### 4. Using the Repository Pattern

```php
use AdaReach\Sms\Repositories\SmsRepository;

$repository = app(SmsRepository::class);

// Get all SMS records
$allSms = $repository->all();

// Find by ID
$sms = $repository->find(1);

// Get today's SMS
$todaySms = $repository->todaySms();

// Get statistics
$stats = $repository->getStatistics();

// Store SMS record
$sms = $repository->create([
    'phone' => '01712345678',
    'message' => 'Your message',
    'sender' => '880XXXXXXXXXX',
    'status' => 'sent',
    'message_id' => 'unique_message_id',
    'cost' => 0.5
]);
```

## Using Outside Laravel

While this package is designed for Laravel, you can use the core API client in any PHP project:

### Installation

```bash
composer require chandan07cse/robi-sms
```

### Standalone Usage

```php
<?php

require 'vendor/autoload.php';

use AdaReach\Sms\AdaReachClient;

// Initialize client
$client = new AdaReachClient(
    username: 'your_username',
    password: 'your_password',
    baseUrl: 'https://api.mobireach.com.bd/api'
);

// Generate token (required before sending SMS)
try {
    $tokenData = $client->generateToken();
    echo "Token generated successfully!\n";
} catch (\Exception $e) {
    echo "Token generation failed: " . $e->getMessage() . "\n";
    exit;
}

// Send SMS
try {
    $result = $client->sendSms([
        'sender' => '880XXXXXXXXXX',
        'receiver' => ['01712345678'],
        'content' => 'Your message here',
        'msgType' => 1,        // 1 = Text, 2 = Unicode
        'requestType' => 1,     // 1 = Single, 2 = Bulk
        'contentType' => 1      // 1 = English, 2 = Bangla
    ]);
    
    print_r($result);
} catch (\Exception $e) {
    echo "SMS sending failed: " . $e->getMessage() . "\n";
}

// Check balance
try {
    $balance = $client->checkBalance();
    echo "GUI Balance: {$balance['guiBalance']} BDT\n";
    echo "API Balance: {$balance['apiBalance']} BDT\n";
} catch (\Exception $e) {
    echo "Balance check failed: " . $e->getMessage() . "\n";
}

// Check message status
try {
    $status = $client->checkStatus('your_message_request_id');
    print_r($status);
} catch (\Exception $e) {
    echo "Status check failed: " . $e->getMessage() . "\n";
}
```

### Standalone Example with Error Handling

```php
<?php

require 'vendor/autoload.php';

use AdaReach\Sms\AdaReachClient;
use AdaReach\Sms\Exceptions\AdaReachException;

class SmsService
{
    private AdaReachClient $client;
    
    public function __construct(string $username, string $password)
    {
        $this->client = new AdaReachClient(
            username: $username,
            password: $password,
            baseUrl: 'https://api.mobireach.com.bd/api'
        );
        
        // Initialize token
        $this->client->generateToken();
    }
    
    public function send(string $phone, string $message, string $sender): array
    {
        try {
            // Format phone number (remove leading 0, add country code)
            $formattedPhone = '880' . ltrim($phone, '0');
            
            return $this->client->sendSms([
                'sender' => $sender,
                'receiver' => [$formattedPhone],
                'content' => $message,
                'msgType' => 1,
                'requestType' => 1,
                'contentType' => 1
            ]);
        } catch (AdaReachException $e) {
            throw new \Exception("SMS sending failed: " . $e->getMessage());
        }
    }
    
    public function getBalance(): array
    {
        return $this->client->checkBalance();
    }
}

// Usage
try {
    $smsService = new SmsService('your_username', 'your_password');
    
    $result = $smsService->send(
        phone: '01712345678',
        message: 'Hello from standalone PHP!',
        sender: '880XXXXXXXXXX'
    );
    
    echo "SMS sent successfully! ID: {$result['requestId']}\n";
    echo "Cost: {$result['charge']} BDT\n";
    
    $balance = $smsService->getBalance();
    echo "Remaining Balance: {$balance['guiBalance']} BDT\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## API Reference

### SMS Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `sender` | string | Yes | Sender ID (13-digit, e.g., 880XXXXXXXXXX) |
| `receiver` | array | Yes | Array of phone numbers (13-digit format) |
| `content` | string | Yes | Message content |
| `msgType` | int | Yes | 1 = Text, 2 = Unicode |
| `requestType` | int | Yes | 1 = Single, 2 = Bulk |
| `contentType` | int | Yes | 1 = English, 2 = Bangla |

### Phone Number Format

Phone numbers must be in 13-digit format: `880XXXXXXXXXX`

Example conversions:
- `01712345678` → `8801712345678`
- `+8801712345678` → `8801712345678`
- `8801712345678` → `8801712345678`

### Message Length & Cost

- **160 characters** = 1 SMS part = ~0.5 BDT
- **161-306 characters** = 2 SMS parts = ~1.0 BDT
- **307-459 characters** = 3 SMS parts = ~1.5 BDT
- And so on...

The dashboard automatically calculates SMS parts and total cost.

## Configuration

### Configuration File (`config/adarearch.php`)

```php
return [
    // API Credentials (fallback if not in database)
    'username' => env('ADAREARCH_USERNAME'),
    'password' => env('ADAREARCH_PASSWORD'),
    'base_url' => env('ADAREARCH_BASE_URL', 'https://api.mobireach.com.bd/api'),
    
    // Default Sender ID
    'default_sender' => env('ADAREARCH_SENDER'),
    
    // Token Cache TTL (minutes)
    'token_cache_ttl' => env('ADAREARCH_TOKEN_CACHE_TTL', 60),
    
    // Settings Cache TTL (minutes)
    'settings_cache_ttl' => env('ADAREARCH_SETTINGS_CACHE_TTL', 60),
    
    // Dashboard Configuration
    'dashboard' => [
        'enabled' => true,
        'route_prefix' => 'adarearch',
        'middleware' => ['web'],
        'author' => 'AdaReach SMS Package',
    ],
];
```

### Database Settings

Settings stored in database take priority over config file:
- `api_username` - API username
- `api_password` - API password (encrypted)
- `api_base_url` - API base URL
- `default_sender` - Default sender ID

## Dashboard Settings

### API Configuration
- Test connection with balance check
- Save encrypted credentials
- Auto-load on application start

### Dashboard Settings
- **Real-time Updates**: Enable/disable WebSocket connections
- **Auto Refresh**: Automatically refresh statistics
- **Notifications**: Show toast notifications for events

## WebSocket (Optional)

The package supports real-time updates via WebSocket:

### Enable Real-time Updates

1. Install Socket.IO dependencies:
```bash
npm install socket.io socket.io-client
```

2. Start the Socket.IO server:
```bash
cd vendor/chandan07cse/robi-sms
node socket-server.js
```

3. Enable in Dashboard → Settings → Real-time Updates

### Disable Real-time Updates

Simply toggle off "Real-time Updates" in Settings. No console errors will appear.

## Events

The package dispatches Laravel events for SMS operations:

```php
// Listen for SMS sent event
Event::listen(\AdaReach\Sms\Events\SmsSent::class, function ($event) {
    // $event->sms - SMS record
    // $event->response - API response
});

// Listen for SMS failed event
Event::listen(\AdaReach\Sms\Events\SmsFailed::class, function ($event) {
    // $event->phone
    // $event->error
});
```

## Error Handling

```php
use AdaReach\Sms\Exceptions\AdaReachException;

try {
    // Sender automatically loaded from config/database
    $result = AdaReach::sendSingle('01712345678', 'Hello!');
} catch (AdaReachException $e) {
    // Handle API errors
    echo "Error Code: " . $e->getCode() . "\n";
    echo "Error Message: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    // Handle other errors
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Common Error Codes

| Code | Description | Solution |
|------|-------------|----------|
| 1504 | Invalid Number | Check phone format (must be 13 digits) |
| 1505 | Invalid Sender | Verify sender ID is correct |
| 1506 | Insufficient Balance | Recharge your account |
| 401 | Authentication Failed | Check username/password |
| 403 | Token Expired | Token will auto-refresh |

## Option 2: Standalone PHP Usage (Without Laravel)

If you want to use this package **outside Laravel** or with **PHP 7.4+**, use the standalone client:

### Installation

```bash
composer require chandan07cse/robi-sms
```

### Quick Example

```php
<?php

require_once 'vendor/autoload.php';

use AdaReach\Sms\StandaloneClient;

// Initialize client with sender ID
$client = new StandaloneClient(
    'your-api-username',
    'your-api-password',
    'YourBrand'  // Your approved sender ID
);

// Send SMS (sender already set)
try {
    $response = $client->sendSms(
        '880XXXXXXXXXX',           // Single recipient
        'Hello! This is a test.'   // Message
    );
    
    echo "SMS sent! Message ID: " . $response['id'] . "\n";
    
    // Check balance
    $balance = $client->checkBalance();
    echo "Balance: " . $balance['balance'] . " SMS\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Bulk SMS

```php
// Send to multiple recipients
$recipients = [
    '880XXXXXXXXXX',
    '880YYYYYYYYYY',
    '880ZZZZZZZZZZ'
];

// Using default sender set in constructor
$response = $client->sendSms(
    $recipients,
    'Bulk message for all'
);

echo "Sent to " . count($response['messages']) . " recipients\n";
```

### Complete Documentation

📖 **[View Full Standalone Documentation](STANDALONE_USAGE.md)**

The standalone documentation includes:
- Detailed API reference
- Error handling examples
- OTP/verification code examples
- Order notification examples
- Bulk campaign examples
- Token management guide
- Troubleshooting tips
- Best practices

**Features:**
- ✅ PHP 7.4+ compatible
- ✅ No Laravel required
- ✅ cURL-based (no Guzzle dependency in standalone mode)
- ✅ Automatic token management
- ✅ File-based token caching
- ✅ Single & bulk SMS support
- ✅ Balance checking
- ✅ Delivery status tracking

## Testing

The package includes comprehensive tests:

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit --filter testSendSingleSms
```

## Examples

### Example 1: Send Welcome SMS

```php
use AdaReach\Sms\Facades\AdaReach;

public function sendWelcomeSms(User $user)
{
    $message = "Welcome {$user->name}! Thank you for registering.";
    
    // Sender automatically loaded from config/database
    return AdaReach::sendSingle(
        receiver: $user->phone,
        message: $message
    );
}
```

### Example 2: Send Bulk Promotional SMS

```php
use AdaReach\Sms\Facades\AdaReach;

public function sendPromotionalSms()
{
    $customers = Customer::where('subscribed', true)
        ->pluck('phone')
        ->toArray();
    
    $message = "🎉 50% OFF on all products! Visit our store today.";
    
    // Sender automatically loaded from config/database
    return AdaReach::sendBulk(
        receivers: $customers,
        message: $message
    );
}
```

### Example 3: Send OTP

```php
use AdaReach\Sms\Facades\AdaReach;

public function sendOtp(string $phone)
{
    $otp = rand(100000, 999999);
    
    // Store OTP in cache/session
    cache(["otp_{$phone}" => $otp], now()->addMinutes(5));
    
    $message = "Your OTP is: {$otp}. Valid for 5 minutes.";
    
    // Sender automatically loaded from config/database
    return AdaReach::sendSingle(
        receiver: $phone,
        message: $message
    );
}
```

### Example 4: Check Balance Before Sending

```php
use AdaReach\Sms\Facades\AdaReach;

public function sendWithBalanceCheck(array $phones, string $message)
{
    // Calculate required balance
    $smsCount = count($phones);
    $parts = ceil(strlen($message) / 160);
    $requiredBalance = $smsCount * $parts * 0.5; // ~0.5 BDT per part
    
    // Check balance
    $balance = AdaReach::checkBalance();
    
    if ($balance['guiBalance'] < $requiredBalance) {
        throw new \Exception("Insufficient balance. Required: {$requiredBalance} BDT");
    }
    
    // Send SMS (sender loaded from config/database)
    return AdaReach::sendBulk(
        receivers: $phones,
        message: $message
    );
}
```

## Troubleshooting

### Issue: "Invalid Number" Error (1504)

**Solution:** Ensure phone numbers are in 13-digit format (`880XXXXXXXXXX`). The package auto-formats numbers, but verify the format:

```php
// Correct formats
'8801712345678'  // ✓
'01712345678'    // ✓ (will be auto-formatted)

// Incorrect formats
'88001712345678' // ✗ (14 digits)
'1712345678'     // ✗ (missing leading 0)
```

### Issue: Token Expired

**Solution:** Tokens auto-refresh. If issues persist, clear cache:

```bash
php artisan cache:clear
```

### Issue: Dashboard Not Loading

**Solution:**
1. Clear browser cache (Ctrl+Shift+R)
2. Republish assets: `php artisan vendor:publish --tag=adarearch-assets --force`
3. Check browser console for errors

### Issue: WebSocket Errors in Console

**Solution:** Disable real-time updates in Settings if you're not using WebSocket.

## Performance Optimization

### 1. Use Queue for Bulk SMS

```php
use Illuminate\Bus\Queueable;

class SendBulkSms implements ShouldQueue
{
    use Queueable;
    
    public function handle()
    {
        AdaReach::sendBulk($this->phones, $this->message, $this->sender);
    }
}

// Dispatch job
SendBulkSms::dispatch($phones, $message, $sender);
```

### 2. Enable Redis Caching

```env
CACHE_DRIVER=redis
```

### 3. Optimize Database Queries

```php
// Use pagination for large datasets
$sms = DB::table('adarearch_sms')
    ->orderBy('created_at', 'desc')
    ->paginate(50);
```

## Changelog

### Version 1.0.0 (2026-01-29)

- Initial release
- SMS sending (single & bulk)
- Beautiful Vue.js dashboard
- Database-backed settings
- Real-time updates (optional)
- SMS analytics and history
- Balance checking
- Cost estimation
- Encrypted credentials

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Security

If you discover any security-related issues, please email chandan07cse@gmail.com instead of using the issue tracker.

## Credits

- **Author**: [chandan07cse](https://github.com/chandan07cse)
- **API Provider**: AdaReach/MobiReach (Robi)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Support

- **Email**: chandan07cse@gmail.com
- **GitHub Issues**: [https://github.com/chandan07cse/robi-sms/issues](https://github.com/chandan07cse/robi-sms/issues)
- **Documentation**: [https://github.com/chandan07cse/robi-sms](https://github.com/chandan07cse/robi-sms)

---

Made with ❤️ for Laravel Developers in Bangladesh
