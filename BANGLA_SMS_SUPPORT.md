# Bangla/Unicode SMS Support

## ✅ Bangla SMS is Now Fully Supported!

Both the Laravel dashboard and standalone client now support Bangla, Emoji, and all Unicode characters with **automatic detection**.

## How It Works

### 🔍 Auto-Detection

The system automatically detects if your message contains:
- **Bangla characters** (বাংলা)
- **Arabic** (العربية)
- **Emoji** (🎉 😊 ❤️)
- **Special symbols** (©, ®, ™, €, ¥)
- **Any non-ASCII characters**

When detected, it automatically sends with `contentType = 2` (Unicode).

## Usage Examples

### 1️⃣ Standalone Client (Auto-Detect)

```php
use Chandan07cse\AdaReach\StandaloneClient;

$client = new StandaloneClient(
    'username',
    'password',
    '01810187701',
    'https://api.mobireach.com.bd'
);

// Bangla SMS - Auto-detected as Unicode
$result = $client->sendSms(
    '01703611109',
    'আপনার OTP কোড: ১২৩৪৫৬'
);

// English SMS - Auto-detected as Regular
$result = $client->sendSms(
    '01703611109',
    'Your OTP code: 123456'
);

// Mixed (Bangla + English) - Auto-detected as Unicode
$result = $client->sendSms(
    '01703611109',
    'আপনার OTP: 123456'
);

// Emoji - Auto-detected as Unicode
$result = $client->sendSms(
    '01703611109',
    '🎉 Congratulations! You won! 🏆'
);
```

### 2️⃣ Standalone Client (Force Unicode)

```php
// Force Unicode mode (5th parameter)
$result = $client->sendSms(
    '01703611109',
    'English text but sent as Unicode',
    null,   // Use default sender
    null,   // No campaign ID
    true    // Force Unicode mode
);

// Force Regular mode (even if Bangla - not recommended)
$result = $client->sendSms(
    '01703611109',
    'Some message',
    null,
    null,
    false   // Force regular mode
);
```

### 3️⃣ Laravel Dashboard

```php
// From dashboard - auto-detects Unicode
POST /adarearch/dashboard/send-sms

{
    "phone": ["01703611109"],
    "sender": "01810187701",
    "message": "আপনার OTP: ১২৩৪৫৬",
    "type": "plain"  // Will auto-detect and send as Unicode
}
```

### 4️⃣ SmsBuilder (Laravel)

```php
use AdaReach\Sms\Facades\AdaReach;

// Auto-detects Bangla/Unicode
$result = AdaReach::from('YourBrand')
    ->to('01703611109')
    ->content('আপনার অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে!')
    ->send();

// English (auto-detected as regular)
$result = AdaReach::from('YourBrand')
    ->to('01703611109')
    ->content('Your account has been created!')
    ->send();

// Force Unicode explicitly
$result = AdaReach::from('YourBrand')
    ->to('01703611109')
    ->content('Some message')
    ->contentType(2)  // Force Unicode
    ->send();
```

### 5️⃣ Bulk Bangla SMS

```php
// Standalone
$phones = ['01712345678', '01812345678', '01912345678'];
$result = $client->sendSms(
    $phones,
    'প্রিয় গ্রাহক, আপনার অর্ডার প্রস্তুত!'
);

// Laravel
$result = AdaReach::from('YourBrand')
    ->toMany($phones)
    ->content('প্রিয় গ্রাহক, আপনার অর্ডার প্রস্তুত!')
    ->send();
```

## Character Limits

| Type | Content Type | Characters per SMS |
|------|-------------|-------------------|
| English | Regular (1) | 160 characters |
| Bangla | Unicode (2) | 70 characters |
| Emoji | Unicode (2) | 70 characters |
| Mixed | Unicode (2) | 70 characters |

### Multi-Part Messages

- **Regular SMS**: 160 chars (1st), then 153 chars per additional SMS
- **Unicode SMS**: 70 chars (1st), then 67 chars per additional SMS

Example:
```php
// Short Bangla - 1 SMS (70 chars or less)
'আপনার OTP: ১২৩৪৫৬' // ✓ 1 SMS

// Long Bangla - 2 SMS (71-137 chars)
'প্রিয় গ্রাহক, আপনার অর্ডার নম্বর #১২৩৪৫৬ সফলভাবে প্রসেস করা হয়েছে।' // ✓ 2 SMS

// Very long - 3 SMS (138-204 chars)
// And so on...
```

## Auto-Detection Logic

The system checks if message contains **any non-ASCII character**:

```php
// Returns TRUE (Unicode needed)
mb_check_encoding('আপনার OTP', 'ASCII')  // FALSE → Unicode
mb_check_encoding('Hello 🎉', 'ASCII')    // FALSE → Unicode
mb_check_encoding('Price: €50', 'ASCII')  // FALSE → Unicode

// Returns FALSE (Regular is fine)
mb_check_encoding('Hello World', 'ASCII') // TRUE → Regular
mb_check_encoding('OTP: 123456', 'ASCII') // TRUE → Regular
```

## Common Use Cases

### 1. OTP Messages (Bangla)
```php
$otp = '১২৩৪৫৬';
$message = "আপনার OTP কোড: {$otp}। এটি ৫ মিনিটের জন্য বৈধ।";

$result = $client->sendSms('01703611109', $message);
// Auto-detected as Unicode ✓
```

### 2. Order Confirmation (Bangla)
```php
$orderNumber = '#১২৩৪৫';
$message = "প্রিয় গ্রাহক, আপনার অর্ডার {$orderNumber} নিশ্চিত করা হয়েছে।";

$result = $client->sendSms('01703611109', $message);
// Auto-detected as Unicode ✓
```

### 3. Balance Alert (Mixed)
```php
$balance = '৫০০.০০';
$message = "আপনার ব্যালেন্স: BDT {$balance}";

$result = $client->sendSms('01703611109', $message);
// Auto-detected as Unicode ✓
```

### 4. Promotional (English with Emoji)
```php
$message = "🎉 FLASH SALE! 50% OFF on all items! 🛍️ Shop now!";

$result = $client->sendSms('01703611109', $message);
// Auto-detected as Unicode ✓
```

## Dashboard UI

The Laravel dashboard automatically:
- ✅ Detects Bangla characters in the message textarea
- ✅ Shows character count (70 for Bangla, 160 for English)
- ✅ Shows SMS count based on message length
- ✅ Sends with correct `contentType` automatically

## Cost Implications

Unicode/Bangla SMS may cost more than regular SMS:
- **Regular SMS**: Standard rate
- **Unicode SMS**: May be 1.5x - 2x regular rate (check with provider)

The system automatically uses regular encoding when possible to minimize costs.

## Testing

Test all formats:

```bash
cd /home/noor/codes/robisms/robi-sms-package/adarearch-laravel
php test-bangla-sms.php
```

## Troubleshooting

### Issue: Bangla shows as ??? or boxes
**Solution**: Message was sent as Regular (contentType=1) instead of Unicode (contentType=2).
- Make sure auto-detection is working
- Or force Unicode mode: `$client->sendSms($phone, $message, null, null, true)`

### Issue: Character limit seems wrong
**Solution**: 
- English: 160 chars per SMS (Regular)
- Bangla: 70 chars per SMS (Unicode)
- Check your message length with `mb_strlen($message)`

### Issue: High cost
**Solution**:
- Unicode/Bangla SMS costs more than regular SMS
- Keep messages short (under 70 chars for Bangla)
- Use English for non-critical messages to save cost

## Summary

| Feature | Status |
|---------|--------|
| Bangla SMS | ✅ Supported |
| Auto-detection | ✅ Enabled |
| Emoji Support | ✅ Supported |
| Arabic/Other Unicode | ✅ Supported |
| Manual Override | ✅ Available |
| Laravel Dashboard | ✅ Auto-detects |
| Standalone Client | ✅ Auto-detects |
| SmsBuilder | ✅ Auto-detects |

---

**Updated:** Version 2.2.0  
**Feature:** Automatic Bangla/Unicode detection  
**Status:** ✅ Production ready
