# ⚡ Quick Setup Guide

## Installation

```bash
composer require chandan07cse/robi-sms
```

## Dashboard Access (Choose One)

### 🎯 Super Simple (Recommended)

Add ONE line to your `index.php`:

```php
require __DIR__ . '/vendor/chandan07cse/robi-sms/routes/sms-dashboard.php';
```

✅ Done! Visit: `http://yoursite.com/sms-dashboard`

---

### 🔧 Alternative Methods

<details>
<summary>Using .htaccess (Apache)</summary>

```apache
RewriteRule ^sms-dashboard/?$ vendor/chandan07cse/robi-sms/public/sms-dashboard.php [L]
```
</details>

<details>
<summary>Copy to Public Directory</summary>

```bash
cp vendor/chandan07cse/robi-sms/public/sms-dashboard.php public/
```

Visit: `http://yoursite.com/sms-dashboard.php`
</details>

---

## Basic Usage

### Send SMS (Standalone PHP)

```php
<?php
require 'vendor/autoload.php';

use AdaReach\StandaloneClient;

// Initialize client
$client = new StandaloneClient(
    'khulnauni',              // username
    'Khulna@1991',            // password
    '8801810187701'           // sender ID
);

// Send SMS (phone auto-normalized: 01XXX → 880XXX)
$response = $client->sendSms('01703611094', 'Hello from SMS!');

// Send Bangla SMS (Unicode auto-detected)
$response = $client->sendSms('01703611094', 'হ্যালো বাংলা!');

// Check balance
$balance = $client->getBalance();
echo "Balance: " . $balance . " BDT";
```

### Phone Number Formats (All Supported)

```php
// All these work automatically:
$client->sendSms('01703611094', 'Test');      // Auto-adds 880
$client->sendSms('8801703611094', 'Test');    // Works as-is
$client->sendSms('+8801703611094', 'Test');   // Auto-removes +
```

### Bangla/Unicode SMS (Auto-Detected)

```php
// English SMS (contentType=1)
$client->sendSms('01703611094', 'Hello World');

// Bangla SMS (contentType=2 auto-detected)
$client->sendSms('01703611094', 'হ্যালো বাংলা!');

// Mixed content (Unicode auto-detected)
$client->sendSms('01703611094', 'Hello হ্যালো 123');
```

---

## Environment Setup (.env)

```env
ADAREARCH_USERNAME=khulnauni
ADAREARCH_PASSWORD=Khulna@1991
ADAREARCH_SENDER_ID=8801810187701
```

---

## Features

✅ PHP 7.4+ Support  
✅ Standalone (No Laravel Required)  
✅ Phone Auto-Normalization (01XXX → 880XXX)  
✅ Bangla/Unicode Auto-Detection  
✅ Ready-to-Use Dashboard  
✅ Token Auto-Caching  
✅ Balance Checking  

---

## Need Help?

- 📖 Full Docs: See `STANDALONE_USAGE.md`
- 🎨 Dashboard Setup: See `DASHBOARD_ACCESS.md`
- 🐛 Phone Formats: See `PHONE_FORMAT_FIX.md`
- 🇧🇩 Bangla Support: See `BANGLA_SMS_SUPPORT.md`

## Support

GitHub: https://github.com/chandan07cse/robi-sms
