<?php

namespace AdaReach\Sms\Storage;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SmsRepository
{
    protected string $prefix;
    protected string $connection;
    protected int $retentionDays;

    public function __construct()
    {
        $this->prefix = config('adarearch.redis.prefix', 'adarearch:');
        $this->connection = config('adarearch.redis.connection', 'default');
        $this->retentionDays = config('adarearch.redis.retention_days', 30);
    }

    /**
     * Store SMS record
     */
    public function store(array $smsData): string
    {
        $id = $smsData['id'] ?? Str::uuid()->toString();
        $timestamp = $smsData['timestamp'] ?? now()->timestamp;
        
        $smsData['id'] = $id;
        $smsData['timestamp'] = $timestamp;
        $smsData['created_at'] = now()->toIso8601String();

        // Store the SMS record
        $this->redis()->hset(
            $this->key('sms', $id),
            'data',
            json_encode($smsData)
        );

        // Add to timeline (sorted set by timestamp)
        $this->redis()->zadd(
            $this->key('timeline'),
            $timestamp,
            $id
        );

        // Add to status index
        $status = $smsData['status'] ?? 'pending';
        $this->redis()->sadd($this->key('status', $status), $id);

        // Add to daily stats
        $date = date('Y-m-d', $timestamp);
        $this->incrementDailyStat($date, $status);

        // Set expiration
        $expireAt = $timestamp + ($this->retentionDays * 86400);
        $this->redis()->expireat($this->key('sms', $id), $expireAt);

        return $id;
    }

    /**
     * Update SMS status
     */
    public function updateStatus(string $id, string $newStatus, array $additionalData = []): bool
    {
        $smsKey = $this->key('sms', $id);
        $data = $this->redis()->hget($smsKey, 'data');

        if (!$data) {
            return false;
        }

        $smsData = json_decode($data, true);
        $oldStatus = $smsData['status'] ?? 'unknown';

        // Update status
        $smsData['status'] = $newStatus;
        $smsData['updated_at'] = now()->toIso8601String();
        $smsData = array_merge($smsData, $additionalData);

        // Save updated data
        $this->redis()->hset($smsKey, 'data', json_encode($smsData));

        // Update status indexes
        $this->redis()->srem($this->key('status', $oldStatus), $id);
        $this->redis()->sadd($this->key('status', $newStatus), $id);

        // Update daily stats
        $date = $smsData['date'] ?? date('Y-m-d');
        $this->decrementDailyStat($date, $oldStatus);
        $this->incrementDailyStat($date, $newStatus);

        return true;
    }

    /**
     * Get SMS by ID
     */
    public function find(string $id): ?array
    {
        $data = $this->redis()->hget($this->key('sms', $id), 'data');
        return $data ? json_decode($data, true) : null;
    }

    /**
     * Get paginated SMS list
     */
    public function paginate(int $page = 1, int $perPage = 50, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get IDs based on filters
        if (!empty($filters['status'])) {
            $ids = $this->redis()->smembers($this->key('status', $filters['status']));
            $ids = array_slice($ids, $offset, $perPage);
        } else {
            // Get from timeline (most recent first)
            $ids = $this->redis()->zrevrange($this->key('timeline'), $offset, $offset + $perPage - 1);
        }

        $items = [];
        foreach ($ids as $id) {
            if ($sms = $this->find($id)) {
                // Apply additional filters
                if ($this->matchesFilters($sms, $filters)) {
                    $items[] = $sms;
                }
            }
        }

        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $this->count($filters),
        ];
    }

    /**
     * Count SMS records
     */
    public function count(array $filters = []): int
    {
        if (!empty($filters['status'])) {
            return $this->redis()->scard($this->key('status', $filters['status']));
        }

        return $this->redis()->zcard($this->key('timeline'));
    }

    /**
     * Get statistics
     */
    public function getStats(string $startDate = null, string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?? date('Y-m-d');

        $stats = [
            'total' => 0,
            'sent' => 0,
            'delivered' => 0,
            'failed' => 0,
            'pending' => 0,
            'daily' => [],
        ];

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($start->lte($end)) {
            $date = $start->format('Y-m-d');
            $dailyStats = $this->getDailyStats($date);
            
            $stats['total'] += $dailyStats['total'];
            $stats['sent'] += $dailyStats['sent'];
            $stats['delivered'] += $dailyStats['delivered'];
            $stats['failed'] += $dailyStats['failed'];
            $stats['pending'] += $dailyStats['pending'];
            
            $stats['daily'][] = array_merge(['date' => $date], $dailyStats);
            
            $start->addDay();
        }

        return $stats;
    }

    /**
     * Get daily statistics
     */
    protected function getDailyStats(string $date): array
    {
        $key = $this->key('stats', $date);
        $stats = $this->redis()->hgetall($key);

        return [
            'total' => (int)($stats['total'] ?? 0),
            'sent' => (int)($stats['sent'] ?? 0),
            'delivered' => (int)($stats['delivered'] ?? 0),
            'failed' => (int)($stats['failed'] ?? 0),
            'pending' => (int)($stats['pending'] ?? 0),
        ];
    }

    /**
     * Increment daily stat
     */
    protected function incrementDailyStat(string $date, string $status): void
    {
        $key = $this->key('stats', $date);
        $this->redis()->hincrby($key, 'total', 1);
        $this->redis()->hincrby($key, $status, 1);
        $this->redis()->expire($key, ($this->retentionDays + 1) * 86400);
    }

    /**
     * Decrement daily stat
     */
    protected function decrementDailyStat(string $date, string $status): void
    {
        $key = $this->key('stats', $date);
        $this->redis()->hincrby($key, 'total', -1);
        $this->redis()->hincrby($key, $status, -1);
    }

    /**
     * Get credit balance
     */
    public function getCreditBalance(): array
    {
        $key = $this->key('credits');
        $data = $this->redis()->get($key);
        return $data ? json_decode($data, true) : ['gui' => 0, 'api' => 0, 'updated_at' => null];
    }

    /**
     * Update credit balance
     */
    public function updateCreditBalance(float $gui, float $api): void
    {
        $key = $this->key('credits');
        $data = [
            'gui' => $gui,
            'api' => $api,
            'updated_at' => now()->toIso8601String(),
        ];
        $this->redis()->set($key, json_encode($data));
    }

    /**
     * Check if SMS matches filters
     */
    protected function matchesFilters(array $sms, array $filters): bool
    {
        if (!empty($filters['phone']) && !str_contains($sms['phone'] ?? '', $filters['phone'])) {
            return false;
        }

        if (!empty($filters['sender']) && ($sms['sender'] ?? '') !== $filters['sender']) {
            return false;
        }

        if (!empty($filters['start_date'])) {
            $smsDate = Carbon::parse($sms['created_at'] ?? now());
            if ($smsDate->lt(Carbon::parse($filters['start_date']))) {
                return false;
            }
        }

        if (!empty($filters['end_date'])) {
            $smsDate = Carbon::parse($sms['created_at'] ?? now());
            if ($smsDate->gt(Carbon::parse($filters['end_date']))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Build Redis key
     */
    protected function key(...$parts): string
    {
        return $this->prefix . implode(':', $parts);
    }

    /**
     * Get Redis instance
     */
    protected function redis()
    {
        return Redis::connection($this->connection);
    }

    /**
     * Clean old records
     */
    public function cleanup(): int
    {
        $cutoff = now()->subDays($this->retentionDays)->timestamp;
        $ids = $this->redis()->zrangebyscore($this->key('timeline'), '-inf', $cutoff);
        
        $count = 0;
        foreach ($ids as $id) {
            $this->delete($id);
            $count++;
        }

        return $count;
    }

    /**
     * Delete SMS record
     */
    protected function delete(string $id): void
    {
        $sms = $this->find($id);
        if ($sms) {
            $status = $sms['status'] ?? 'unknown';
            $this->redis()->srem($this->key('status', $status), $id);
            $this->redis()->zrem($this->key('timeline'), $id);
            $this->redis()->del($this->key('sms', $id));
        }
    }

    /**
     * Get hourly distribution of SMS
     */
    public function getHourlyDistribution(string $startDate = null, string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-m-d');
        $endDate = $endDate ?? date('Y-m-d');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $hourlyData = array_fill(0, 24, 0);
        
        // Get all SMS IDs in the date range
        $startTimestamp = $start->timestamp;
        $endTimestamp = $end->timestamp;
        
        $ids = $this->redis()->zrangebyscore(
            $this->key('timeline'),
            $startTimestamp,
            $endTimestamp
        );

        foreach ($ids as $id) {
            if ($sms = $this->find($id)) {
                $created = Carbon::parse($sms['created_at'] ?? now());
                $hour = (int)$created->format('H');
                $hourlyData[$hour]++;
            }
        }

        return $hourlyData;
    }

    /**
     * Get peak hour from hourly distribution
     */
    public function getPeakHour(string $startDate = null, string $endDate = null): int
    {
        $hourlyData = $this->getHourlyDistribution($startDate, $endDate);
        $maxCount = max($hourlyData);
        $peakHour = array_search($maxCount, $hourlyData);
        
        return $peakHour !== false ? $peakHour : 0;
    }

    /**
     * Get top senders
     */
    public function getTopSenders(string $startDate = null, string $endDate = null, int $limit = 5): array
    {
        $startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?? date('Y-m-d');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $senders = [];
        
        // Get all SMS IDs in the date range
        $ids = $this->redis()->zrangebyscore(
            $this->key('timeline'),
            $start->timestamp,
            $end->timestamp
        );

        foreach ($ids as $id) {
            if ($sms = $this->find($id)) {
                $sender = $sms['sender'] ?? 'Unknown';
                
                if (!isset($senders[$sender])) {
                    $senders[$sender] = [
                        'sender' => $sender,
                        'total' => 0,
                        'delivered' => 0,
                        'failed' => 0,
                    ];
                }
                
                $senders[$sender]['total']++;
                
                if (($sms['status'] ?? '') === 'delivered') {
                    $senders[$sender]['delivered']++;
                } elseif (($sms['status'] ?? '') === 'failed') {
                    $senders[$sender]['failed']++;
                }
            }
        }

        // Calculate success rate for each sender
        foreach ($senders as $sender => &$data) {
            $data['success_rate'] = $data['total'] > 0 
                ? round(($data['delivered'] / $data['total']) * 100, 1)
                : 0;
        }

        // Sort by total count descending
        uasort($senders, function($a, $b) {
            return $b['total'] - $a['total'];
        });

        return array_slice(array_values($senders), 0, $limit);
    }

    /**
     * Get average response time (in seconds)
     */
    public function getAverageResponseTime(string $startDate = null, string $endDate = null): float
    {
        $startDate = $startDate ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $endDate ?? date('Y-m-d');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $totalResponseTime = 0;
        $count = 0;
        
        // Get all SMS IDs in the date range
        $ids = $this->redis()->zrangebyscore(
            $this->key('timeline'),
            $start->timestamp,
            $end->timestamp
        );

        foreach ($ids as $id) {
            if ($sms = $this->find($id)) {
                if (isset($sms['response_time']) && is_numeric($sms['response_time'])) {
                    $totalResponseTime += $sms['response_time'];
                    $count++;
                }
            }
        }

        return $count > 0 ? round($totalResponseTime / $count, 2) : 0;
    }

    /**
     * Get delivery time analysis
     */
    public function getDeliveryTimeAnalysis(string $startDate = null, string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $endDate ?? date('Y-m-d');

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $deliveryTimes = [];
        
        while ($start->lte($end)) {
            $date = $start->format('Y-m-d');
            $deliveryTimes[] = [
                'date' => $date,
                'avg_delivery_time' => $this->getDailyAverageDeliveryTime($date),
            ];
            $start->addDay();
        }

        return $deliveryTimes;
    }

    /**
     * Get average delivery time for a specific date
     */
    protected function getDailyAverageDeliveryTime(string $date): float
    {
        $start = Carbon::parse($date)->startOfDay();
        $end = Carbon::parse($date)->endOfDay();

        $totalDeliveryTime = 0;
        $count = 0;
        
        $ids = $this->redis()->zrangebyscore(
            $this->key('timeline'),
            $start->timestamp,
            $end->timestamp
        );

        foreach ($ids as $id) {
            if ($sms = $this->find($id)) {
                if (isset($sms['delivery_time']) && is_numeric($sms['delivery_time'])) {
                    $totalDeliveryTime += $sms['delivery_time'];
                    $count++;
                }
            }
        }

        return $count > 0 ? round($totalDeliveryTime / $count, 2) : 0;
    }

    /**
     * Store SMS with response time
     */
    public function storeWithResponseTime(array $smsData, float $responseTime): string
    {
        $smsData['response_time'] = $responseTime;
        return $this->store($smsData);
    }

    /**
     * Update SMS with delivery time
     */
    public function updateWithDeliveryTime(string $id, string $newStatus, float $deliveryTime, array $additionalData = []): bool
    {
        $additionalData['delivery_time'] = $deliveryTime;
        return $this->updateStatus($id, $newStatus, $additionalData);
    }
}

