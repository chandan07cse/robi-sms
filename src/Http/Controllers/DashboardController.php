<?php

namespace AdaReach\Sms\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use AdaReach\Sms\Storage\SmsRepository;
use AdaReach\Sms\AdaReachClient;

class DashboardController extends Controller
{
    protected SmsRepository $repository;

    public function __construct(SmsRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show login form
     */
    public function loginForm()
    {
        // If already authenticated, redirect to dashboard
        if (session()->has('adarearch_authenticated')) {
            return redirect(config('adarearch.dashboard.path', 'sms-dashboard'));
        }
        
        return view('adarearch::login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');

        // Get credentials from config
        $configUsername = config('adarearch.dashboard.username');
        $configPassword = config('adarearch.dashboard.password');

        // Validate credentials
        if ($username === $configUsername && Hash::check($password, $configPassword)) {
            session()->put('adarearch_authenticated', true);
            session()->put('adarearch_username', $username);
            
            return response()->json([
                'success' => true,
                'redirect' => '/' . config('adarearch.dashboard.path', 'sms-dashboard')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid username or password'
        ], 401);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $request->session()->forget('adarearch_authenticated');
        $request->session()->forget('adarearch_username');
        
        return redirect(config('adarearch.dashboard.path', 'sms-dashboard') . '/login');
    }

    /**
     * Show dashboard view
     */
    public function index()
    {
        return view('adarearch::dashboard');
    }

    /**
     * Get SMS list with pagination and filters
     */
    public function list(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 50);
        
        $filters = array_filter([
            'status' => $request->get('status'),
            'phone' => $request->get('phone'),
            'sender' => $request->get('sender'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ]);

        return response()->json(
            $this->repository->paginate($page, $perPage, $filters)
        );
    }

    /**
     * Get single SMS details
     */
    public function show(string $id)
    {
        $sms = $this->repository->find($id);
        
        if (!$sms) {
            return response()->json(['error' => 'SMS not found'], 404);
        }

        return response()->json($sms);
    }

    /**
     * Get statistics
     */
    public function stats(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $stats = $this->repository->getStats($startDate, $endDate);
        
        // Add analytics data
        $stats['avg_response_time'] = $this->repository->getAverageResponseTime($startDate, $endDate);
        $stats['peak_hour'] = $this->repository->getPeakHour($startDate, $endDate);
        $stats['hourly_distribution'] = $this->repository->getHourlyDistribution($startDate, $endDate);
        $stats['top_senders'] = $this->repository->getTopSenders($startDate, $endDate, 5);
        $stats['delivery_time_analysis'] = $this->repository->getDeliveryTimeAnalysis($startDate, $endDate);

        return response()->json($stats);
    }

    /**
     * Get credit balance
     */
    public function credits()
    {
        $cached = $this->repository->getCreditBalance();
        
        // Try to fetch fresh data from API
        try {
            $client = app(AdaReachClient::class);
            $balance = $client->checkBalance();
            
            $credits = [
                'gui' => $balance['guiBalance'] ?? 0,
                'api' => $balance['apiBalance'] ?? 0,
                'updated_at' => now()->toIso8601String(),
            ];
            
            $this->repository->updateCreditBalance($credits['gui'], $credits['api']);
            
            return response()->json($credits);
        } catch (\Exception $e) {
            // Return cached data if API fails
            return response()->json($cached);
        }
    }

    /**
     * Get dashboard overview
     */
    public function overview(Request $request)
    {
        $period = $request->get('period', '7days');
        
        switch ($period) {
            case 'today':
                $startDate = date('Y-m-d');
                break;
            case '7days':
                $startDate = date('Y-m-d', strtotime('-7 days'));
                break;
            case '30days':
                $startDate = date('Y-m-d', strtotime('-30 days'));
                break;
            case '90days':
                $startDate = date('Y-m-d', strtotime('-90 days'));
                break;
            default:
                $startDate = date('Y-m-d', strtotime('-7 days'));
                break;
        }

        $stats = $this->repository->getStats($startDate, date('Y-m-d'));
        
        // Calculate success rate
        $successRate = $stats['total'] > 0 
            ? round(($stats['delivered'] / $stats['total']) * 100, 2)
            : 0;

        // Calculate failure rate
        $failureRate = $stats['total'] > 0
            ? round(($stats['failed'] / $stats['total']) * 100, 2)
            : 0;

        // Get hourly distribution for today
        $hourlyData = $this->getHourlyDistribution();

        return response()->json([
            'period' => $period,
            'stats' => $stats,
            'success_rate' => $successRate,
            'failure_rate' => $failureRate,
            'hourly' => $hourlyData,
            'top_senders' => $this->getTopSenders(),
            'status_distribution' => [
                ['name' => 'Delivered', 'value' => $stats['delivered'], 'color' => '#10b981'],
                ['name' => 'Sent', 'value' => $stats['sent'], 'color' => '#3b82f6'],
                ['name' => 'Failed', 'value' => $stats['failed'], 'color' => '#ef4444'],
                ['name' => 'Pending', 'value' => $stats['pending'], 'color' => '#f59e0b'],
            ],
        ]);
    }

    /**
     * Get hourly distribution for today
     */
    protected function getHourlyDistribution(): array
    {
        $data = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $data[] = [
                'hour' => sprintf('%02d:00', $hour),
                'count' => 0, // This would need additional tracking in Redis
            ];
        }
        return $data;
    }

    /**
     * Get top senders
     */
    protected function getTopSenders(): array
    {
        // This would need additional tracking in Redis
        return [];
    }

    /**
     * Retry failed SMS
     */
    public function retry(string $id)
    {
        $sms = $this->repository->find($id);
        
        if (!$sms) {
            return response()->json(['error' => 'SMS not found'], 404);
        }

        if ($sms['status'] !== 'failed') {
            return response()->json(['error' => 'Only failed SMS can be retried'], 400);
        }

        try {
            $client = app(AdaReachClient::class);
            
            $result = $client->sendSms([
                'recipient' => $sms['phone'],
                'sender' => $sms['sender'],
                'message' => $sms['message'],
                'type' => $sms['type'] ?? 'plain',
            ]);

            $this->repository->updateStatus($id, 'sent', [
                'retried_at' => now()->toIso8601String(),
                'response' => $result,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'SMS retried successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export SMS data
     */
    public function export(Request $request)
    {
        $filters = array_filter([
            'status' => $request->get('status'),
            'phone' => $request->get('phone'),
            'sender' => $request->get('sender'),
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
        ]);

        $format = $request->get('format', 'csv');

        // Get all matching records
        $page = 1;
        $allRecords = [];
        
        do {
            $result = $this->repository->paginate($page, 100, $filters);
            $allRecords = array_merge($allRecords, $result['data']);
            $page++;
        } while (count($result['data']) > 0);

        if ($format === 'json') {
            return response()->json($allRecords);
        }

        // CSV export
        $csv = "ID,Phone,Sender,Message,Status,Type,Created At\n";
        foreach ($allRecords as $record) {
            $csv .= sprintf(
                "%s,%s,%s,\"%s\",%s,%s,%s\n",
                $record['id'] ?? '',
                $record['phone'] ?? '',
                $record['sender'] ?? '',
                str_replace('"', '""', $record['message'] ?? ''),
                $record['status'] ?? '',
                $record['type'] ?? '',
                $record['created_at'] ?? ''
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="sms-export-' . date('Y-m-d') . '.csv"');
    }

    /**
     * Send SMS from dashboard
     */
    public function sendSms(Request $request)
    {
        try {
            $client = app(AdaReachClient::class);
            
            $phones = $request->input('phone', []);
            if (!is_array($phones)) {
                $phones = [$phones];
            }

            $sender = $request->input('sender');
            $message = $request->input('message');
            $type = $request->input('type', 'plain');

            if (empty($phones) || empty($sender) || empty($message)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone, sender, and message are required'
                ], 400);
            }

            // Auto-detect Unicode/Bangla if type is 'plain'
            if ($type === 'plain' && !mb_check_encoding($message, 'ASCII')) {
                $type = 'unicode';  // Auto-detect Bangla, Emoji, and other Unicode
            }

            $results = [];
            
            foreach ($phones as $phone) {
                try {
                    $response = $client->sendSms([
                        'sender' => $sender,
                        'receiver' => [$phone],  // API expects 'receiver' as array
                        'content' => $message,   // API expects 'content' not 'message'
                        'msgType' => 'T',        // T = Transactional, P = Promotional
                        'requestType' => 'S',    // S = Single, B = Bulk
                        'contentType' => $type === 'unicode' ? 2 : 1  // 1 = Regular, 2 = Unicode
                    ]);

                    // Determine status from response
                    // API returns: status="SUCCESS" + errorCode=0 for success
                    //              status="FAILED" + errorCode=non-zero for failure
                    $status = 'sent';  // Default to success
                    
                    // Check errorCode first (most reliable)
                    if (isset($response['errorCode']) && $response['errorCode'] != 0) {
                        $status = 'failed';
                    }
                    // Also check status field as secondary indicator
                    elseif (isset($response['status']) && strtoupper($response['status']) === 'FAILED') {
                        $status = 'failed';
                    }

                    // Store in repository with response time
                    $smsId = $this->repository->store([
                        'phone' => $phone,
                        'sender' => $sender,
                        'message' => $message,
                        'status' => $status,
                        'type' => $type,
                        'response' => $response,
                        'response_time' => $response['response_time'] ?? 0,
                        'source' => 'dashboard'
                    ]);

                    $results[] = [
                        'phone' => $phone,
                        'status' => $status,
                        'status' => 'sent',
                        'id' => $smsId
                    ];

                    // Broadcast event
                    event(new \AdaReach\Sms\Events\SmsSent([
                        'id' => $smsId,
                        'phone' => $phone,
                        'sender' => $sender,
                        'message' => $message,
                        'status' => 'sent',
                        'type' => $type,
                        'timestamp' => time()
                    ]));

                } catch (\Exception $e) {
                    $results[] = [
                        'phone' => $phone,
                        'status' => 'failed',
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'SMS sending completed',
                'results' => $results,
                'total' => count($phones),
                'sent' => count(array_filter($results, fn($r) => $r['status'] === 'sent')),
                'failed' => count(array_filter($results, fn($r) => $r['status'] === 'failed'))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all settings
     */
    public function getSettings()
    {
        try {
            $settings = \AdaReach\Sms\Models\Setting::getAll();
            
            return response()->json([
                'success' => true,
                'settings' => $settings
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $validated = $request->validate([
                'api_username' => 'sometimes|string|max:255',
                'api_password' => 'sometimes|string|max:255',
                'default_sender' => 'sometimes|string|max:255',
                'api_base_url' => 'sometimes|string|url',
            ]);

            foreach ($validated as $key => $value) {
                $encrypted = $key === 'api_password';
                \AdaReach\Sms\Models\Setting::set($key, $value, $encrypted);
            }

            // Clear settings cache
            \AdaReach\Sms\Models\Setting::clearCache();

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test API connection with current settings
     */
    public function testConnection()
    {
        try {
            // Create a new client with current settings to test
            $username = \AdaReach\Sms\Models\Setting::get('api_username') ?? config('adarearch.username');
            $password = \AdaReach\Sms\Models\Setting::get('api_password') ?? config('adarearch.password');
            $baseUrl = \AdaReach\Sms\Models\Setting::get('api_base_url') ?? config('adarearch.base_url');

            if (!$username || !$password) {
                return response()->json([
                    'success' => false,
                    'message' => 'API credentials not configured'
                ], 400);
            }

            $client = new AdaReachClient($username, $password, $baseUrl);
            
            // Try to generate token first
            try {
                $client->generateToken();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed: ' . $e->getMessage()
                ], 401);
            }
            
            // If token generation succeeds, check balance
            $balance = $client->checkBalance();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful',
                'balance' => $balance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

