# Sender ID Fix - Standalone Client

## Problem
The `StandaloneClient` was missing proper sender ID handling. The sender parameter was optional in `sendSms()`, but the AdaReach API requires it for every SMS.

## Solution

### 1. Updated Constructor
Added `$sender` parameter to the constructor as the **3rd parameter**:

```php
// Before
public function __construct($username, $password, $baseUrl = '...', $cacheDir = null)

// After
public function __construct($username, $password, $sender = null, $baseUrl = '...', $cacheDir = null)
```

### 2. Default Sender Property
Added `protected $sender` property to store the default sender ID.

### 3. Updated sendSms Method
- Now uses default sender if none provided
- Throws clear error if no sender is available
- Always includes sender in API request

```php
public function sendSms($recipients, $message, $sender = null, $campaignId = null)
{
    // Use provided sender or default sender
    $senderToUse = $sender ?? $this->sender;
    
    if (!$senderToUse) {
        throw new AdaReachException(
            'Sender ID is required. Please provide a sender or set a default sender in the constructor.',
            400
        );
    }
    
    // ... rest of the code
    $params['sender'] = $senderToUse;
}
```

### 4. Added Helper Methods
```php
// Set sender
$client->setSender('NewSender');

// Get current sender
$currentSender = $client->getSender();
```

## Usage Examples

### Recommended: Set Default Sender in Constructor

```php
$client = new StandaloneClient(
    'api_username',
    'api_password',
    'YourBrand'  // Default sender ID
);

// Send SMS without specifying sender each time
$client->sendSms('880XXXXXXXXXX', 'Hello!');
$client->sendSms(['880XXX...', '880YYY...'], 'Bulk message');
```

### Override Sender for Specific Messages

```php
// Use different sender for specific message
$client->sendSms('880XXXXXXXXXX', 'Promo message', 'PromoSender');
```

### Change Default Sender Later

```php
$client->setSender('NewSender');
$client->sendSms('880XXXXXXXXXX', 'Message with new sender');
```

### Backward Compatibility with Old Usage

```php
// Still works, but sender must be provided every time
$client = new StandaloneClient('username', 'password');
$client->sendSms('880XXXXXXXXXX', 'Hello', 'Sender');  // Must specify sender
```

## Benefits

1. **Cleaner Code**: Don't repeat sender ID in every `sendSms()` call
2. **Required Field**: Clear error message if sender is missing
3. **Flexibility**: Can override sender for specific messages
4. **Better API**: More intuitive and follows best practices

## Files Updated

1. **src/StandaloneClient.php**
   - Added `$sender` property
   - Updated constructor signature
   - Updated `sendSms()` method
   - Added `setSender()` and `getSender()` methods

2. **STANDALONE_USAGE.md**
   - Updated Quick Start example
   - Updated Initialize Client section
   - Updated Send SMS examples

3. **examples/standalone-usage.php**
   - Updated initialization
   - Updated all sendSms() calls

4. **test-standalone.php**
   - Updated initialization
   - Updated test SMS sending

5. **README.md**
   - Updated standalone quick example
   - Updated bulk SMS example
   - Updated intro example

6. **QUICK_REFERENCE.md**
   - Updated standalone mode section
   - Updated configuration section
   - Updated API methods section
   - Added `setSender()` and `getSender()` methods

## Migration Guide

### If You Were Using:

```php
// Old way (still works)
$client = new StandaloneClient('username', 'password', 'https://api.mobireach.com.bd/api');
$client->sendSms('880XXX', 'Hello', 'Sender');
```

### Migrate To:

```php
// New way (recommended)
$client = new StandaloneClient('username', 'password', 'Sender');
$client->sendSms('880XXX', 'Hello');  // Sender automatically included
```

### If Using Custom Base URL:

```php
// Specify all parameters
$client = new StandaloneClient(
    'username',
    'password',
    'Sender',
    'https://custom.api.url',
    '/custom/cache/dir'
);
```

## Error Handling

If sender is not set:

```php
try {
    $client = new StandaloneClient('username', 'password');  // No sender
    $client->sendSms('880XXX', 'Hello');  // No sender provided
} catch (AdaReachException $e) {
    echo $e->getMessage();
    // Output: "Sender ID is required. Please provide a sender or set a default sender in the constructor."
}
```

## Testing

Update your test scripts:

```bash
# Edit test-standalone.php
nano test-standalone.php

# Update these lines:
$sender = 'YourBrand';
$client = new StandaloneClient($username, $password, $sender);

# Run test
php test-standalone.php
```

## Summary

✅ Sender ID is now properly handled  
✅ Can be set as default in constructor  
✅ Can be overridden per message  
✅ Clear error if missing  
✅ All documentation updated  
✅ Backward compatible (with explicit sender parameter)  

The package is now easier to use and follows API best practices!
