# Quick Start Guide - Robi SMS

Get started with Robi SMS package in 5 minutes!

## Installation

```bash
composer require chandan07cse/robi-sms
```

## Configuration

1. Publish config:
```bash
php artisan vendor:publish --tag=adarearch-config
```

2. Add to `.env`:
```env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_DEFAULT_SENDER=YourSenderID
```

## Your First SMS

```php
use AdaReach\Sms\Facades\AdaReach;

// Send SMS
$result = AdaReach::message()
    ->to('01712345678')
    ->content('Hello from Robi SMS!')
    ->send();

// Get message ID
echo $result['messageId'];
```

## Common Examples

### Send OTP
```php
$otp = rand(100000, 999999);

AdaReach::message()
    ->to($user->phone)
    ->content("Your OTP: {$otp}")
    ->transactional()
    ->send();
```

### Send Bulk SMS
```php
$phones = ['01712345678', '01812345678', '01912345678'];

AdaReach::message()
    ->toMany($phones)
    ->content('Limited time offer!')
    ->promotional()
    ->send();
```

### Check Balance
```php
$balance = AdaReach::checkBalance();
echo "Balance: ৳" . $balance['apiBalance'];
```

### Check Status
```php
$status = AdaReach::checkStatus(
    sender: 'YourSender',
    messageId: '626314298741755904',
    receiver: '8801712345678'
);

if ($status['status'] === 'SUCCESS') {
    echo 'Delivered!';
}
```

## Error Handling

```php
use AdaReach\Sms\Exceptions\AdaReachException;

try {
    AdaReach::message()->to($phone)->content($msg)->send();
} catch (AdaReachException $e) {
    Log::error('SMS failed: ' . $e->getMessage());
}
```

## Need More?

- 📖 [Full Documentation](README.md)
- 💡 [Usage Examples](USAGE_GUIDE.md)
- 🚀 [Deployment Guide](DEPLOYMENT.md)

## Support

Issues? Open a ticket: https://github.com/chandan07cse/robi-sms/issues
