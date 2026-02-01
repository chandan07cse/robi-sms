# Analytics API Quick Reference

## Backend Methods

### SmsRepository Methods

#### `getHourlyDistribution(string $startDate, string $endDate): array`
Returns SMS count for each hour of the day (0-23).

**Parameters:**
- `$startDate`: Start date (YYYY-MM-DD), default: today
- `$endDate`: End date (YYYY-MM-DD), default: today

**Returns:**
```php
[0, 5, 10, 15, 20, 25, 30, ..., 5, 0] // 24 elements
```

**Usage:**
```php
$hourly = $repository->getHourlyDistribution('2026-01-01', '2026-01-31');
```

---

#### `getPeakHour(string $startDate, string $endDate): int`
Returns the hour with the highest SMS volume.

**Parameters:**
- `$startDate`: Start date (YYYY-MM-DD), default: today
- `$endDate`: End date (YYYY-MM-DD), default: today

**Returns:**
```php
14 // Integer from 0-23
```

**Usage:**
```php
$peakHour = $repository->getPeakHour('2026-01-01', '2026-01-31');
echo "Peak hour: {$peakHour}:00";
```

---

#### `getTopSenders(string $startDate, string $endDate, int $limit = 5): array`
Returns top senders by SMS volume with statistics.

**Parameters:**
- `$startDate`: Start date, default: 30 days ago
- `$endDate`: End date, default: today
- `$limit`: Number of results, default: 5

**Returns:**
```php
[
    [
        'sender' => 'COMPANY',
        'total' => 1250,
        'delivered' => 1180,
        'failed' => 70,
        'success_rate' => 94.4
    ],
    // ... more senders
]
```

**Usage:**
```php
$topSenders = $repository->getTopSenders('2026-01-01', '2026-01-31', 10);
```

---

#### `getAverageResponseTime(string $startDate, string $endDate): float`
Returns average API response time in seconds.

**Parameters:**
- `$startDate`: Start date, default: 30 days ago
- `$endDate`: End date, default: today

**Returns:**
```php
1.85 // Seconds (float)
```

**Usage:**
```php
$avgTime = $repository->getAverageResponseTime('2026-01-01', '2026-01-31');
echo "Average response: {$avgTime}s";
```

---

#### `getDeliveryTimeAnalysis(string $startDate, string $endDate): array`
Returns daily average delivery times.

**Parameters:**
- `$startDate`: Start date, default: 7 days ago
- `$endDate`: End date, default: today

**Returns:**
```php
[
    [
        'date' => '2026-01-01',
        'avg_delivery_time' => 2.5
    ],
    [
        'date' => '2026-01-02',
        'avg_delivery_time' => 1.8
    ],
    // ... more dates
]
```

**Usage:**
```php
$deliveryTimes = $repository->getDeliveryTimeAnalysis('2026-01-01', '2026-01-31');
```

---

#### `storeWithResponseTime(array $smsData, float $responseTime): string`
Store SMS record with response time.

**Parameters:**
- `$smsData`: SMS data array
- `$responseTime`: Response time in seconds

**Returns:**
```php
'uuid-string' // SMS ID
```

**Usage:**
```php
$id = $repository->storeWithResponseTime([
    'phone' => '8801XXXXXXXXX',
    'sender' => 'TEST',
    'message' => 'Hello',
    'status' => 'sent'
], 1.234);
```

---

#### `updateWithDeliveryTime(string $id, string $newStatus, float $deliveryTime, array $additionalData = []): bool`
Update SMS status with delivery time.

**Parameters:**
- `$id`: SMS ID
- `$newStatus`: New status (delivered, failed, etc.)
- `$deliveryTime`: Delivery time in seconds
- `$additionalData`: Additional fields to update

**Returns:**
```php
true // Success
false // Not found
```

**Usage:**
```php
$repository->updateWithDeliveryTime(
    $smsId,
    'delivered',
    2.5,
    ['delivered_at' => now()]
);
```

---

## API Endpoints

### GET `/api/stats`

**Query Parameters:**
- `start_date` (optional): YYYY-MM-DD format
- `end_date` (optional): YYYY-MM-DD format

**Response:**
```json
{
  "total": 5000,
  "sent": 4800,
  "delivered": 4500,
  "failed": 300,
  "pending": 200,
  "daily": [
    {
      "date": "2026-01-01",
      "total": 150,
      "sent": 145,
      "delivered": 140,
      "failed": 5,
      "pending": 0
    }
  ],
  "avg_response_time": 1.85,
  "peak_hour": 14,
  "hourly_distribution": [0, 5, 10, 15, ...],
  "top_senders": [
    {
      "sender": "COMPANY",
      "total": 1250,
      "delivered": 1180,
      "failed": 70,
      "success_rate": 94.4
    }
  ],
  "delivery_time_analysis": [
    {
      "date": "2026-01-01",
      "avg_delivery_time": 2.5
    }
  ]
}
```

**cURL Example:**
```bash
curl -X GET "http://localhost/api/stats?start_date=2026-01-01&end_date=2026-01-31"
```

**JavaScript Example:**
```javascript
const response = await fetch('/api/stats?start_date=2026-01-01&end_date=2026-01-31');
const stats = await response.json();
console.log(stats.avg_response_time);
```

---

## Frontend Usage

### Vue Component (Analytics.vue)

**Computed Properties:**

```javascript
// Average Response Time
const avgResponseTime = computed(() => {
  const time = stats.value?.avg_response_time || 0;
  return time > 0 ? `${time}s` : 'N/A';
});

// Peak Hour
const peakHour = computed(() => {
  const hour = stats.value?.peak_hour;
  if (hour === undefined || hour === null) return 'N/A';
  return `${String(hour).padStart(2, '0')}:00`;
});

// Hourly Activity Chart
const hourlyActivity = computed(() => {
  const hourlyData = stats.value?.hourly_distribution || Array(24).fill(0);
  return {
    labels: Array.from({ length: 24 }, (_, i) => `${i}:00`),
    datasets: [{
      label: 'SMS Count',
      data: hourlyData,
      backgroundColor: '#8b5cf6'
    }]
  };
});

// Top Senders
const topSenders = computed(() => stats.value?.top_senders || []);

// Delivery Time Chart
const deliveryTimeData = computed(() => {
  const deliveryAnalysis = stats.value?.delivery_time_analysis || [];
  return {
    labels: deliveryAnalysis.map(d => format(new Date(d.date), 'MMM dd')),
    datasets: [{
      label: 'Avg Delivery Time (seconds)',
      data: deliveryAnalysis.map(d => d.avg_delivery_time),
      borderColor: '#10b981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4
    }]
  };
});
```

---

## Laravel Usage Examples

### In Controllers

```php
use AdaReach\Sms\Storage\SmsRepository;

class AnalyticsController extends Controller
{
    protected SmsRepository $repository;

    public function __construct(SmsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        return view('analytics', [
            'stats' => $this->repository->getStats($startDate, $endDate),
            'peak_hour' => $this->repository->getPeakHour($startDate, $endDate),
            'top_senders' => $this->repository->getTopSenders($startDate, $endDate),
            'avg_response' => $this->repository->getAverageResponseTime($startDate, $endDate),
        ]);
    }
}
```

### In Artisan Commands

```php
use AdaReach\Sms\Storage\SmsRepository;

class AnalyticsReport extends Command
{
    protected $signature = 'analytics:report {--period=30}';
    protected $description = 'Generate analytics report';

    public function handle(SmsRepository $repository)
    {
        $days = $this->option('period');
        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $stats = $repository->getStats($startDate, $endDate);
        $peakHour = $repository->getPeakHour($startDate, $endDate);
        $topSenders = $repository->getTopSenders($startDate, $endDate);

        $this->info("Analytics Report ({$days} days)");
        $this->line("Total SMS: {$stats['total']}");
        $this->line("Peak Hour: {$peakHour}:00");
        
        $this->table(
            ['Sender', 'Total', 'Success Rate'],
            collect($topSenders)->map(fn($s) => [
                $s['sender'],
                $s['total'],
                $s['success_rate'] . '%'
            ])
        );
    }
}
```

---

## Performance Tips

### Caching

```php
use Illuminate\Support\Facades\Cache;

// Cache hourly distribution for 5 minutes
$hourlyData = Cache::remember("hourly_{$startDate}_{$endDate}", 300, function() use ($repository, $startDate, $endDate) {
    return $repository->getHourlyDistribution($startDate, $endDate);
});
```

### Batch Processing

```php
// Get multiple analytics in parallel
$analytics = [
    'stats' => $repository->getStats($startDate, $endDate),
    'peak_hour' => $repository->getPeakHour($startDate, $endDate),
    'top_senders' => $repository->getTopSenders($startDate, $endDate, 5),
    'avg_response' => $repository->getAverageResponseTime($startDate, $endDate),
];
```

---

## Testing Examples

### PHPUnit

```php
public function test_hourly_distribution()
{
    $repository = app(SmsRepository::class);
    
    // Store test SMS at different hours
    for ($hour = 0; $hour < 24; $hour++) {
        $repository->store([
            'phone' => '8801XXXXXXXXX',
            'sender' => 'TEST',
            'message' => 'Test',
            'status' => 'sent',
            'created_at' => now()->setHour($hour)->toIso8601String(),
            'timestamp' => now()->setHour($hour)->timestamp,
        ]);
    }
    
    $distribution = $repository->getHourlyDistribution(
        now()->format('Y-m-d'),
        now()->format('Y-m-d')
    );
    
    $this->assertCount(24, $distribution);
    $this->assertGreaterThan(0, array_sum($distribution));
}
```

---

## Troubleshooting

### No Data Showing

1. Check Redis connection
2. Verify SMS records exist in date range
3. Check `response_time` and `delivery_time` fields are being stored
4. Review browser console for API errors

### Slow Performance

1. Add caching for frequently accessed data
2. Limit date ranges
3. Consider pre-aggregating hourly data
4. Use background jobs for heavy calculations

---

**Last Updated**: January 31, 2026  
**Package**: chandan07cse/robi-sms v2.1.0+
