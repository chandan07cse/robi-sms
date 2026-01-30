# Phone Number Format Guide

## ✅ Good News: You Don't Need to Change Your Database!

The standalone client now **automatically normalizes** phone numbers, so you can use your existing database format without any changes.

## Automatic Normalization

The client automatically converts these formats:

| Your DB Format | Auto-Converts To | Works? |
|----------------|------------------|--------|
| `01703611109` | `8801703611109` | ✅ YES |
| `8801703611109` | `8801703611109` | ✅ YES |
| `+8801703611109` | `8801703611109` | ✅ YES |
| `1703611109` | `8801703611109` | ✅ YES |

## Usage Examples

### Single SMS (DB Format)
```php
use Chandan07cse\AdaReach\StandaloneClient;

$client = new StandaloneClient(
    'your_username',
    'your_password',
    '01810187701',  // ← You can use 01... format
    'https://api.mobireach.com.bd'
);

// Use number directly from your database
$phone = '01703611109';  // ← No need to add 880!
$result = $client->sendSms($phone, 'Your message');
```

### Bulk SMS from Database
```php
// Query your database
$users = DB::table('users')->pluck('phone')->toArray();
// Result: ['01712345678', '01812345678', '01912345678']

// Send directly - auto-normalized!
$result = $client->sendSms(
    $users,  // ← Array of 01... numbers
    'Bulk message to all users'
);
```

### Real Laravel Example
```php
<?php

use Chandan07cse\AdaReach\StandaloneClient;
use Illuminate\Support\Facades\DB;

// Initialize client
$client = new StandaloneClient(
    config('adarearch.api_username'),
    config('adarearch.api_password'),
    config('adarearch.default_sender'),
    config('adarearch.api_base_url')
);

// Get users from database (phones are stored as 01XXXXXXXXX)
$users = DB::table('users')
    ->where('active', true)
    ->get();

foreach ($users as $user) {
    try {
        // Send directly - no need to format!
        $result = $client->sendSms(
            $user->phone,  // e.g., '01712345678'
            "Hello {$user->name}, your OTP is: 123456"
        );
        
        echo "✓ Sent to {$user->phone}\n";
    } catch (Exception $e) {
        echo "✗ Failed for {$user->phone}: {$e->getMessage()}\n";
    }
}
```

### Non-Laravel PHP Script
```php
<?php

require 'vendor/autoload.php';

use Chandan07cse\AdaReach\StandaloneClient;

// Connect to your database
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');

// Get phone numbers (stored as 01XXXXXXXXX in DB)
$stmt = $pdo->query("SELECT phone FROM customers WHERE active = 1");
$phones = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Initialize SMS client
$client = new StandaloneClient(
    'your_username',
    'your_password',
    '01810187701',
    'https://api.mobireach.com.bd'
);

// Send to all - no formatting needed!
$result = $client->sendSms(
    $phones,  // Array: ['01712345678', '01812345678', ...]
    'Special offer just for you!'
);

if ($result['status'] === 'SUCCESS') {
    echo "✓ Sent to " . count($phones) . " customers!\n";
}
```

## How It Works

The client has a built-in `normalizePhoneNumber()` method that:

1. **Removes non-numeric characters** (except +)
2. **Detects format**:
   - If starts with `880` (13 digits) → keeps as-is
   - If starts with `0` (11 digits) → removes `0`, adds `880`
   - If starts with `1` (10 digits) → adds `880`
   - If has `+` prefix → removes it, processes rest
3. **Returns normalized number** in `880XXXXXXXXXX` format

## Sender ID

Same auto-normalization works for sender IDs:

```php
// All these work:
$client = new StandaloneClient('user', 'pass', '01810187701');
$client = new StandaloneClient('user', 'pass', '8801810187701');
$client = new StandaloneClient('user', 'pass', '+8801810187701');

// Or use text sender:
$client = new StandaloneClient('user', 'pass', 'YourBrand');
```

## Mixed Formats

Even if your database has mixed formats, they all work:

```php
$phones = [
    '01712345678',      // ✓ Converts to 8801712345678
    '8801812345678',    // ✓ Stays as 8801812345678
    '+8801912345678',   // ✓ Converts to 8801912345678
    '1612345678',       // ✓ Converts to 8801612345678
];

$client->sendSms($phones, 'Works with all formats!');
```

## Summary

### ✅ You Can Use:
- `01703611109` ← **Most common in BD databases**
- `8801703611109` ← Already normalized
- `+8801703611109` ← International format
- `1703611109` ← Without country code or 0

### 🎉 No Database Changes Needed!

Just use your existing phone numbers directly. The client handles everything automatically.

### ⚡ Performance Note

Normalization happens in PHP (not via API call), so there's **zero performance impact**. It's just a string operation before sending the API request.

## Testing

To verify it works with your database format:

```php
// Test with one number from your DB
$testPhone = '01703611109';  // Your DB format

$result = $client->sendSms($testPhone, 'Test message');

if ($result['status'] === 'SUCCESS') {
    echo "✓ Your DB format works perfectly!\n";
}
```

---

**Updated in:** Version 2.1.1  
**Feature:** Automatic phone number normalization  
**Status:** ✅ Production ready
