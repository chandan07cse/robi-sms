# Redis Logging & SMS Storage

## Overview

The AdaReach SMS package **automatically logs every SMS** sent through the package to Redis. This enables powerful analytics, history tracking, and debugging capabilities.

## Storage Architecture

### Redis Data Structures

#### 1. Timeline (Sorted Set)
```redis
ZSET adarearch:timeline
Score: Unix timestamp (when SMS was sent)
Member: SMS ID (UUID)
```

**Purpose**: Time-based indexing for fast date range queries

**Example**:
```bash
redis-cli
> ZRANGE adarearch:timeline 0 -1 WITHSCORES
1) "550e8400-e29b-41d4-a716-446655440000"  # SMS ID
2) "1738368000"                             # Timestamp
3) "550e8400-e29b-41d4-a716-446655440001"
4) "1738368120"
```

#### 2. SMS Records (Hashes)
```redis
HASH adarearch:sms:{id}
Fields: data, status, created_at, updated_at
```

**Purpose**: Store complete SMS details

**Example**:
```bash
redis-cli
> HGETALL adarearch:sms:550e8400-e29b-41d4-a716-446655440000

"data" => "{\"phone\":\"01712345678\",\"sender\":\"TEST\",\"message\":\"Hello\"...}"
"status" => "delivered"
"created_at" => "1738368000"
"updated_at" => "1738368120"
```

#### 3. Phone Index (Sets)
```redis
SET adarearch:phone:{normalized_phone}
Members: SMS IDs
```

**Purpose**: Find all SMS sent to a specific phone number

**Example**:
```bash
redis-cli
> SMEMBERS adarearch:phone:8801712345678
1) "550e8400-e29b-41d4-a716-446655440000"
2) "550e8400-e29b-41d4-a716-446655440001"
```

#### 4. Sender Index (Sets)
```redis
SET adarearch:sender:{sender_id}
Members: SMS IDs
```

**Purpose**: Find all SMS sent from a specific sender ID

**Example**:
```bash
redis-cli
> SMEMBERS adarearch:sender:TEST
1) "550e8400-e29b-41d4-a716-446655440000"
2) "550e8400-e29b-41d4-a716-446655440002"
```

#### 5. Status Index (Sets)
```redis
SET adarearch:status:{status}
Members: SMS IDs
```

**Purpose**: Find all SMS by status (sent, delivered, failed, pending)

**Example**:
```bash
redis-cli
> SMEMBERS adarearch:status:delivered
1) "550e8400-e29b-41d4-a716-446655440000"
2) "550e8400-e29b-41d4-a716-446655440003"
```

## Automatic Logging

### When SMS is Sent

Every time you send an SMS, it's automatically logged to Redis:

**Using Facade:**
```php
use AdaReach\Sms\Facades\Sms;

$result = Sms::from('TEST')
    ->to('01712345678')
    ->content('Hello World')
    ->send();

// ✅ Automatically stored in Redis with:
// - SMS ID (UUID)
// - Phone number
// - Sender ID
// - Message content
// - Status (sent/failed)
// - Timestamp
// - Response time (API latency)
// - API response data
```

**Using Dashboard:**
```
1. Visit: http://localhost:8000/sms-dashboard
2. Go to "Send SMS" page
3. Fill form and submit
4. ✅ Automatically logged to Redis
```

**Using Client Directly:**
```php
$client = new \AdaReach\Sms\AdaReachClient();
$response = $client->sendSms([
    'sender' => 'TEST',
    'receiver' => ['01712345678'],
    'content' => 'Hello',
    'msgType' => 'T',
    'requestType' => 'S',
    'contentType' => 1
]);

// ⚠️ When using client directly, you must manually store:
$repository = app(\AdaReach\Sms\Storage\SmsRepository::class);
$smsId = $repository->store([
    'phone' => '01712345678',
    'sender' => 'TEST',
    'message' => 'Hello',
    'status' => 'sent',
    'type' => 'plain',
    'response' => $response,
    'response_time' => $response['response_time'] ?? 0,
]);
```

### Data Stored for Each SMS

```json
{
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "phone": "8801712345678",
    "original_phone": "01712345678",
    "sender": "TEST",
    "message": "Hello World",
    "status": "sent",
    "type": "plain",
    "response": {
        "messageId": "12345",
        "status": "success",
        "description": "Message sent successfully"
    },
    "response_time": 1.234,
    "source": "dashboard",
    "created_at": "2026-02-01T10:30:00Z",
    "updated_at": "2026-02-01T10:30:00Z"
}
```

## Implementation Details

### SmsRepository Methods

#### `store(array $smsData): string`
Stores a new SMS record

```php
$repository = app(\AdaReach\Sms\Storage\SmsRepository::class);

$smsId = $repository->store([
    'phone' => '01712345678',
    'sender' => 'TEST',
    'message' => 'Hello World',
    'status' => 'sent',
    'type' => 'plain',
    'response' => ['messageId' => '12345'],
    'response_time' => 1.234,
    'source' => 'api'
]);

// Returns: UUID string
echo $smsId; // "550e8400-e29b-41d4-a716-446655440000"
```

#### `storeWithResponseTime(array $smsData, float $responseTime): string`
Convenience method to store SMS with response time

```php
$smsId = $repository->storeWithResponseTime([
    'phone' => '01712345678',
    'sender' => 'TEST',
    'message' => 'Hello',
    'status' => 'sent',
    'type' => 'plain',
    'response' => $response
], 1.234);
```

#### `update(string $id, array $data): bool`
Update an existing SMS record

```php
$repository->update($smsId, [
    'status' => 'delivered',
    'updated_at' => time()
]);
```

#### `updateWithDeliveryTime(string $id, string $status, int $time, array $data): bool`
Update with delivery time tracking

```php
$repository->updateWithDeliveryTime(
    $smsId,
    'delivered',
    time(),
    ['dlr_status' => 'DELIVRD']
);
```

#### `find(string $id): ?array`
Retrieve SMS by ID

```php
$sms = $repository->find($smsId);

if ($sms) {
    echo $sms['phone'];
    echo $sms['message'];
    echo $sms['status'];
}
```

#### `delete(string $id): bool`
Delete SMS record (also removes from all indexes)

```php
$repository->delete($smsId);
```

### Where Logging Happens

#### 1. DashboardController::sendSms()
**File**: `src/Http/Controllers/DashboardController.php` (Line 383)

```php
public function sendSms(Request $request)
{
    // ... validation ...
    
    foreach ($phones as $phone) {
        try {
            $response = $client->sendSms([...]);

            // ✅ Automatic logging here
            $smsId = $this->repository->store([
                'phone' => $phone,
                'sender' => $sender,
                'message' => $message,
                'status' => 'sent',
                'type' => $type,
                'response' => $response,
                'response_time' => $response['response_time'] ?? 0,
                'source' => 'dashboard'
            ]);

        } catch (\Exception $e) {
            // Failed SMS also logged
        }
    }
}
```

#### 2. SmsService (when using Facade)
**File**: `src/Services/SmsService.php`

```php
public function send(): mixed
{
    $response = $this->client->sendSms([...]);
    
    // ✅ Automatic logging
    $smsId = $this->repository->store([
        'phone' => $this->to,
        'sender' => $this->from,
        'message' => $this->message,
        'status' => 'sent',
        'type' => $this->type,
        'response' => $response,
        'response_time' => $response['response_time'] ?? 0,
        'source' => 'facade'
    ]);
    
    return $response;
}
```

## Querying SMS Data

### Get All SMS (Paginated)
```php
$repository = app(\AdaReach\Sms\Storage\SmsRepository::class);

$result = $repository->all(
    page: 1,
    perPage: 50,
    filters: []
);

echo "Total: " . $result['total'];
foreach ($result['data'] as $sms) {
    echo $sms['phone'] . ": " . $sms['message'];
}
```

### Filter by Status
```php
$result = $repository->all(
    page: 1,
    perPage: 50,
    filters: ['status' => 'delivered']
);
```

### Filter by Phone Number
```php
$result = $repository->all(
    page: 1,
    perPage: 50,
    filters: ['phone' => '01712345678']
);
```

### Filter by Sender
```php
$result = $repository->all(
    page: 1,
    perPage: 50,
    filters: ['sender' => 'TEST']
);
```

### Filter by Date Range
```php
$result = $repository->all(
    page: 1,
    perPage: 50,
    filters: [
        'start_date' => '2026-02-01',
        'end_date' => '2026-02-28'
    ]
);
```

### Combined Filters
```php
$result = $repository->all(
    page: 1,
    perPage: 50,
    filters: [
        'status' => 'sent',
        'sender' => 'TEST',
        'start_date' => '2026-02-01'
    ]
);
```

## Analytics from Redis Data

All analytics in the dashboard are powered by Redis data:

### Total SMS Count
```php
$stats = $repository->getStats('2026-02-01', '2026-02-28');
echo "Total: " . $stats['total'];
echo "Sent: " . $stats['sent'];
echo "Delivered: " . $stats['delivered'];
echo "Failed: " . $stats['failed'];
```

### Hourly Distribution
```php
$hourlyData = $repository->getHourlyDistribution('2026-02-01', '2026-02-28');
// Returns: [0, 5, 10, 8, 0, 0, ..., 3, 1] (24 values)
```

### Top Senders
```php
$topSenders = $repository->getTopSenders('2026-02-01', '2026-02-28', 5);
// Returns:
// [
//     ['sender' => 'TEST', 'total' => 100, 'delivered' => 95, 'failed' => 5, 'success_rate' => 95.0],
//     ['sender' => 'PROMO', 'total' => 80, 'delivered' => 78, 'failed' => 2, 'success_rate' => 97.5],
//     ...
// ]
```

### Average Response Time
```php
$avgResponseTime = $repository->getAverageResponseTime('2026-02-01', '2026-02-28');
echo "Average API response time: " . $avgResponseTime . "s";
```

### Peak Hour
```php
$peakHour = $repository->getPeakHour('2026-02-01', '2026-02-28');
echo "Busiest hour: " . $peakHour . ":00";
```

## Data Retention

### Automatic Cleanup
SMS records are automatically deleted after the retention period (default: 30 days)

**Configuration**: `config/adarearch.php`
```php
'redis' => [
    'retention_days' => env('ADAREARCH_RETENTION_DAYS', 30),
],
```

### Manual Cleanup
```bash
# Delete SMS older than 30 days
php artisan adarearch:cleanup --days=30

# Delete all SMS
php artisan adarearch:cleanup --all
```

## Redis Commands for Debugging

### Check Total SMS Count
```bash
redis-cli ZCARD adarearch:timeline
```

### Get Latest 10 SMS
```bash
redis-cli ZREVRANGE adarearch:timeline 0 9 WITHSCORES
```

### Get SMS Details
```bash
redis-cli HGETALL adarearch:sms:{sms-id}
```

### Count by Status
```bash
redis-cli SCARD adarearch:status:sent
redis-cli SCARD adarearch:status:delivered
redis-cli SCARD adarearch:status:failed
```

### Find SMS by Phone
```bash
redis-cli SMEMBERS adarearch:phone:8801712345678
```

### Find SMS by Sender
```bash
redis-cli SMEMBERS adarearch:sender:TEST
```

### Check Memory Usage
```bash
redis-cli INFO memory
```

## Performance Considerations

### 1. Indexing
- ✅ **Timeline**: O(log N) for date range queries
- ✅ **Status/Phone/Sender**: O(1) for membership check, O(N) for full set scan
- ✅ **Hash storage**: O(1) for get/set

### 2. Memory Usage
- Each SMS record: ~500 bytes
- 1 million SMS: ~500 MB
- Timeline index: ~24 bytes per SMS
- Other indexes: ~16 bytes per SMS per index

**Estimated total**: ~550-600 MB for 1 million SMS

### 3. Optimization Tips

**Use Redis Persistence:**
```bash
# In redis.conf
save 900 1      # Save after 900 sec if at least 1 key changed
save 300 10     # Save after 300 sec if at least 10 keys changed
save 60 10000   # Save after 60 sec if at least 10000 keys changed
```

**Use Redis Cluster for Large Scale:**
```php
// config/database.php
'redis' => [
    'cluster' => true,
    'clusters' => [
        'default' => [
            ['host' => '127.0.0.1', 'port' => 6379],
            ['host' => '127.0.0.1', 'port' => 6380],
        ],
    ],
],
```

**Implement Data Partitioning:**
```php
// Store by date
$dateKey = "adarearch:timeline:" . date('Y-m-d');
```

## Troubleshooting

### Issue: No SMS showing in dashboard
**Solution**:
```bash
# Check if Redis is running
redis-cli PING
# Expected: PONG

# Check SMS count
redis-cli ZCARD adarearch:timeline
# Expected: Number > 0

# Check config
php artisan config:clear
```

### Issue: Redis memory full
**Solution**:
```bash
# Clean old data
php artisan adarearch:cleanup --days=7

# Or increase maxmemory
redis-cli CONFIG SET maxmemory 2gb
```

### Issue: Slow queries
**Solution**:
```bash
# Monitor slow commands
redis-cli SLOWLOG GET 10

# Check keys count
redis-cli DBSIZE
```

## Summary

The Redis logging system provides:
- ✅ **Automatic logging** - Every SMS is stored
- ✅ **Fast queries** - O(log N) time-based search
- ✅ **Multiple indexes** - Phone, sender, status
- ✅ **Analytics ready** - Built-in aggregation methods
- ✅ **Low overhead** - Minimal performance impact
- ✅ **Persistent storage** - Data survives restarts
- ✅ **Scalable** - Handles millions of SMS

All SMS sent through the package (Facade, Dashboard, or Controller) are automatically logged to Redis with no additional configuration required!
