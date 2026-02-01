# Token Management & Auto-Refresh

## Overview

The AdaReach SMS package now includes intelligent token management with automatic expiration detection and refresh capabilities.

## Token Lifecycle

### 1. Token Generation
```php
$client = new AdaReachClient();
$tokenData = $client->generateToken();

// Returns:
[
    'token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
    'refresh_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',
    'expires_in' => 3600 // seconds
]
```

### 2. Token Caching
- **Cache Duration**: 55 minutes (token valid for 60 minutes)
- **Cache Key**: `adarearch_tokens_{username}`
- **Cached Data**:
  ```php
  [
      'token' => '...',
      'refresh_token' => '...',
      'expires_at' => Carbon instance,
      'cached_at' => Carbon instance
  ]
  ```

### 3. Token Validation (`ensureValidToken()`)

The package automatically checks token validity before every API call:

```php
protected function ensureValidToken(): void
{
    // Step 1: Check if token exists
    if (!$this->token) {
        $this->generateToken();
        return;
    }

    // Step 2: Check expiration from cache
    $tokenData = Cache::get("adarearch_tokens_{$this->username}");
    
    if (!$tokenData || !isset($tokenData['expires_at'])) {
        // No expiration data - refresh or regenerate
        if ($this->refreshToken) {
            $this->refreshToken();
        } else {
            $this->generateToken();
        }
        return;
    }

    // Step 3: Proactive refresh (5 minutes before expiry)
    if (now()->addMinutes(5)->greaterThan($tokenData['expires_at'])) {
        if ($this->refreshToken) {
            $this->refreshToken();
        } else {
            $this->generateToken();
        }
    }
}
```

### 4. Automatic Retry on 401 Unauthorized

If a token has expired and the API returns 401, the package automatically:
1. Clears the token cache
2. Refreshes the token (or generates new if refresh fails)
3. Retries the API call once

**Example in `sendSms()` method:**
```php
public function sendSms(array $params): array
{
    $this->ensureValidToken();

    $response = Http::withToken($this->token)
        ->post("{$this->baseUrl}/sms/send", $params);

    // Automatic retry on 401
    if ($response->status() === 401) {
        $this->clearTokenCache();
        $this->refreshToken();
        
        // Retry with fresh token
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/sms/send", $params);
    }

    if ($response->failed()) {
        throw new AdaReachException('Failed to send SMS');
    }

    return $response->json();
}
```

## Token Refresh

### Automatic Refresh
- Happens **5 minutes before expiration**
- Uses refresh token from cache
- Falls back to generating new token if refresh fails

### Manual Refresh
```php
$client = new AdaReachClient();
$newTokenData = $client->refreshToken();

// Returns new tokens (same structure as generateToken)
```

### Refresh Token Endpoint
```http
POST /auth/token/refresh
Authorization: Bearer {refresh_token}
```

## Benefits

### 1. **Zero Downtime**
- Proactive refresh prevents token expiration errors
- Automatic retry on 401 ensures requests succeed

### 2. **Reduced API Calls**
- Token reused from cache for 55 minutes
- Only refreshes when needed

### 3. **Better Error Handling**
- Graceful fallback from refresh to generation
- Automatic recovery from token issues

### 4. **Thread-Safe**
- Cache-based token storage works across requests
- Multiple workers can share tokens

## Configuration

### Cache Driver
The package uses Laravel's default cache driver. For best performance with multiple workers:

```env
# .env
CACHE_DRIVER=redis
```

### Token TTL
Default: 55 minutes (hard-coded)
Token validity: 60 minutes (AdaReach API)

To customize, modify `AdaReachClient::cacheTokens()`:
```php
protected function cacheTokens(): void
{
    $cacheKey = "adarearch_tokens_{$this->username}";
    $expiresAt = now()->addMinutes(55); // Change this value
    
    Cache::put($cacheKey, [
        'token' => $this->token,
        'refresh_token' => $this->refreshToken,
        'expires_at' => $expiresAt,
        'cached_at' => now(),
    ], $expiresAt);
}
```

## Manual Token Management

### Clear Token Cache
```php
$client = new AdaReachClient();
$client->clearTokenCache();
```

### Force New Token Generation
```php
$client->clearTokenCache();
$client->generateToken();
```

### Check Current Token
```php
$cacheKey = "adarearch_tokens_" . config('adarearch.username');
$tokenData = Cache::get($cacheKey);

if ($tokenData) {
    echo "Token expires at: " . $tokenData['expires_at'];
    echo "Cached at: " . $tokenData['cached_at'];
}
```

## Debugging

### Enable Debug Mode
```php
// In your code
Log::info('Token status', [
    'has_token' => !empty($client->token),
    'has_refresh' => !empty($client->refreshToken),
    'cache_data' => Cache::get("adarearch_tokens_" . config('adarearch.username'))
]);
```

### Common Issues

#### Issue: "Failed to generate token"
**Cause**: Invalid API credentials
**Solution**: Check `.env` file:
```env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
```

#### Issue: "Token expired" errors
**Cause**: Cache cleared or cache driver not persistent
**Solution**: 
- Use Redis cache driver for production
- Check if cache is working: `php artisan cache:clear`

#### Issue: "Failed to refresh token"
**Cause**: Refresh token expired or invalid
**Solution**: Package automatically generates new token, but check:
```php
$client->clearTokenCache();
$client->generateToken();
```

## Implementation Details

### Methods Updated

1. **`ensureValidToken()`** - Enhanced with expiration checking
2. **`cacheTokens()`** - Now stores expiration timestamp
3. **`sendSms()`** - Added 401 retry logic
4. **`checkStatus()`** - Added 401 retry logic
5. **`checkBalance()`** - Added 401 retry logic

### Files Modified
- `src/AdaReachClient.php` - Main token management
- `src/StandaloneClient.php` - Similar implementation for standalone usage

## Testing

### Test Token Generation
```bash
cd /path/to/sms-test-app
php artisan tinker

>>> $client = new \AdaReach\Sms\AdaReachClient();
>>> $tokens = $client->generateToken();
>>> dd($tokens);
```

### Test Token Refresh
```bash
php artisan tinker

>>> $client = new \AdaReach\Sms\AdaReachClient();
>>> $client->generateToken();
>>> sleep(1);
>>> $newTokens = $client->refreshToken();
>>> dd($newTokens);
```

### Test Automatic Retry
```bash
# Send SMS with expired token simulation
php artisan tinker

>>> $client = new \AdaReach\Sms\AdaReachClient();
>>> $client->clearTokenCache(); // Force token issue
>>> // Next call will auto-generate token
>>> $result = $client->sendSms([
...     'sender' => 'TEST',
...     'receiver' => ['01712345678'],
...     'content' => 'Test message',
...     'msgType' => 'T',
...     'requestType' => 'S',
...     'contentType' => 1
... ]);
>>> dd($result);
```

## Best Practices

1. **Use Redis Cache in Production**
   ```env
   CACHE_DRIVER=redis
   ```

2. **Don't Clear Cache Frequently**
   - Avoid `php artisan cache:clear` in production
   - Use `php artisan config:clear` instead

3. **Monitor Token Usage**
   - Log failed token refreshes
   - Alert on repeated 401 errors

4. **Handle Exceptions Gracefully**
   ```php
   try {
       $result = $client->sendSms($params);
   } catch (\AdaReach\Sms\Exceptions\AdaReachException $e) {
       if ($e->getCode() == 401) {
           // Token issue - already retried once
           Log::error('Token refresh failed after retry', [
               'message' => $e->getMessage()
           ]);
       }
   }
   ```

## Summary

The token management system provides:
- ✅ **Automatic expiration detection** (5 min proactive refresh)
- ✅ **Automatic refresh** using refresh tokens
- ✅ **Automatic retry** on 401 errors
- ✅ **Cache-based token reuse** (55 min)
- ✅ **Graceful fallback** to token generation
- ✅ **Zero-downtime operation**

No manual intervention required - the package handles everything automatically!
