# Installation & Usage Guide

## Quick Start

### 1. Install the Package

```bash
composer require adarearch/laravel-sms
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=adarearch-config
```

### 3. Configure Environment

Add to your `.env`:

```env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_DEFAULT_SENDER=YourSenderID
```

### 4. Send Your First SMS

```php
use AdaReach\Sms\Facades\AdaReach;

$result = AdaReach::message()
    ->to('01712345678')
    ->content('Hello from Laravel!')
    ->send();

echo "Message ID: " . $result['messageId'];
```

## Common Use Cases

### 1. OTP Verification

```php
public function sendOtp($phone, $otp)
{
    return AdaReach::message()
        ->from('MyApp')
        ->to($phone)
        ->content("Your OTP is: {$otp}. Valid for 5 minutes.")
        ->transactional()
        ->send();
}
```

### 2. Order Notifications

```php
public function notifyOrder($phone, $orderId, $amount)
{
    $message = "Order #{$orderId} confirmed! Amount: ৳{$amount}";
    
    return AdaReach::message()
        ->to($phone)
        ->content($message)
        ->transactional()
        ->send();
}
```

### 3. Promotional Campaigns

```php
public function sendPromotion($phones, $offer)
{
    return AdaReach::message()
        ->toMany($phones) // Up to 400 numbers
        ->content($offer)
        ->promotional()
        ->send();
}
```

### 4. Delivery Tracking

```php
public function checkDelivery($messageId, $phone)
{
    return AdaReach::checkStatus(
        sender: config('adarearch.default_sender'),
        messageId: $messageId,
        receiver: $phone
    );
}
```

### 5. Balance Monitoring

```php
public function getBalance()
{
    $balance = AdaReach::checkBalance();
    
    if ($balance['apiBalance'] < 100) {
        // Alert admin
        Mail::to('admin@example.com')
            ->send(new LowBalanceAlert($balance));
    }
    
    return $balance;
}
```

## Laravel Integration Examples

### Using in Jobs

```php
namespace App\Jobs;

use AdaReach\Sms\Facades\AdaReach;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public string $phone,
        public string $message
    ) {}

    public function handle()
    {
        AdaReach::message()
            ->to($this->phone)
            ->content($this->message)
            ->send();
    }
}

// Dispatch the job
SendSmsJob::dispatch('01712345678', 'Your order is ready!');
```

### Using in Notifications

```php
namespace App\Notifications;

use AdaReach\Sms\Facades\AdaReach;
use Illuminate\Notifications\Notification;

class OrderShipped extends Notification
{
    public function via($notifiable)
    {
        return ['sms'];
    }

    public function toSms($notifiable)
    {
        return AdaReach::message()
            ->to($notifiable->phone)
            ->content("Your order #{$this->order->id} has been shipped!")
            ->send();
    }
}
```

### Using in Events

```php
// Event
namespace App\Events;

class UserRegistered
{
    public function __construct(public User $user) {}
}

// Listener
namespace App\Listeners;

use AdaReach\Sms\Facades\AdaReach;

class SendWelcomeSms
{
    public function handle(UserRegistered $event)
    {
        AdaReach::message()
            ->to($event->user->phone)
            ->content("Welcome to our platform, {$event->user->name}!")
            ->send();
    }
}
```

## Error Handling Best Practices

```php
use AdaReach\Sms\Exceptions\AdaReachException;
use Illuminate\Support\Facades\Log;

try {
    $result = AdaReach::message()
        ->to($phone)
        ->content($message)
        ->send();
        
    // Store message ID for tracking
    DB::table('sms_logs')->insert([
        'message_id' => $result['messageId'],
        'phone' => $phone,
        'cost' => $result['msgCost'],
        'sent_at' => now(),
    ]);
    
} catch (AdaReachException $e) {
    
    // Log the error
    Log::error('SMS sending failed', [
        'phone' => $phone,
        'error_code' => $e->getCode(),
        'error_message' => $e->getMessage(),
    ]);
    
    // Handle specific errors
    match ($e->getCode()) {
        1506 => throw new Exception('Insufficient SMS balance'),
        1503 => throw new Exception('Sender ID not approved'),
        1514 => throw new Exception('Too many recipients (max 400)'),
        default => throw new Exception('SMS service unavailable'),
    };
}
```

## Tips & Best Practices

### 1. Use Queues for Bulk SMS
```php
foreach ($users as $user) {
    SendSmsJob::dispatch($user->phone, $message)
        ->onQueue('sms');
}
```

### 2. Monitor Your Balance
```php
Schedule::daily()->at('09:00')
    ->call(function () {
        $balance = AdaReach::checkBalance();
        
        if ($balance['apiBalance'] < 1000) {
            // Send alert
        }
    });
```

### 3. Validate Phone Numbers
```php
$request->validate([
    'phone' => [
        'required',
        'regex:/^(?:\+?880|0)?1[3-9]\d{8}$/',
    ],
]);
```

### 4. Cache Frequently Used Data
```php
$balance = Cache::remember('sms_balance', 300, function () {
    return AdaReach::checkBalance();
});
```

### 5. Respect Promotional Time Limits
```php
$hour = now()->hour;

if ($hour >= 9 && $hour < 20) {
    // Safe to send promotional SMS
    AdaReach::message()->promotional()->send();
}
```

## Troubleshooting

### Token Issues
```php
// Clear token cache if you're having authentication issues
AdaReach::clearTokenCache();
AdaReach::generateToken();
```

### Testing in Development
```php
// Use HTTP fake in tests
use Illuminate\Support\Facades\Http;

Http::fake([
    '*/auth/tokens' => Http::response([
        'token' => 'test-token',
        'refresh_token' => 'test-refresh'
    ]),
    '*/sms/send' => Http::response([
        'status' => 'SUCCESS',
        'messageId' => 123456
    ])
]);
```

## Support

For issues and questions:
- Email: helpdesk@adaglobal.com
- Create an issue on GitHub
