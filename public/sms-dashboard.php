<?php
/**
 * SMS Dashboard Router for Standalone Projects
 * 
 * This file provides a standalone dashboard accessible via /sms-dashboard
 * 
 * Installation:
 * 1. Copy this file to your project's public directory
 * 2. Configure your web server to route /sms-dashboard to this file
 * 3. Or use PHP built-in server: php -S localhost:8095
 * 
 * Apache .htaccess example:
 * RewriteRule ^sms-dashboard$ vendor/chandan07cse/robi-sms/public/sms-dashboard.php [L]
 * 
 * Nginx example:
 * location /sms-dashboard {
 *     rewrite ^/sms-dashboard$ /vendor/chandan07cse/robi-sms/public/sms-dashboard.php last;
 * }
 */

// Start session
session_start();

// Auto-detect vendor autoload location
$vendorPaths = [
    __DIR__ . '/../../autoload.php',           // If in vendor/chandan07cse/robi-sms/public
    __DIR__ . '/../vendor/autoload.php',        // If in project root
    __DIR__ . '/vendor/autoload.php',           // If in public directory
    __DIR__ . '/../../../autoload.php',         // Composer vendor structure
];

$autoloadFound = false;
foreach ($vendorPaths as $path) {
    if (file_exists($path)) {
        require $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    die('<h1>Error</h1><p>Could not find autoload.php. Please run: <code>composer install</code></p>');
}

use Chandan07cse\AdaReach\StandaloneClient;

// ============================================
// CONFIGURATION
// Try to load from environment or config file
// ============================================

// Option 1: From environment variables
$config = [
    'username' => getenv('ADAREARCH_USERNAME') ?: 'your_username',
    'password' => getenv('ADAREARCH_PASSWORD') ?: 'your_password',
    'sender' => getenv('ADAREARCH_SENDER') ?: 'your_sender_id',
    'base_url' => getenv('ADAREARCH_BASE_URL') ?: 'https://api.mobireach.com.bd'
];

// Option 2: From config file (if exists)
$configFile = __DIR__ . '/../config/adarearch.php';
if (file_exists($configFile)) {
    $fileConfig = require $configFile;
    $config = [
        'username' => $fileConfig['api_username'] ?? $config['username'],
        'password' => $fileConfig['api_password'] ?? $config['password'],
        'sender' => $fileConfig['default_sender'] ?? $config['sender'],
        'base_url' => $fileConfig['api_base_url'] ?? $config['base_url']
    ];
}

// Option 3: From .env file (simple parser)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key === 'ADAREARCH_USERNAME') $config['username'] = $value;
        if ($key === 'ADAREARCH_PASSWORD') $config['password'] = $value;
        if ($key === 'ADAREARCH_SENDER') $config['sender'] = $value;
        if ($key === 'ADAREARCH_BASE_URL') $config['base_url'] = $value;
    }
}

// Check if configured
$isConfigured = $config['username'] !== 'your_username' && $config['password'] !== 'your_password';

// Initialize client (only if configured)
$client = null;
if ($isConfigured) {
    try {
        $client = new StandaloneClient(
            $config['username'],
            $config['password'],
            $config['sender'],
            $config['base_url']
        );
    } catch (Exception $e) {
        $configError = $e->getMessage();
    }
}

// Handle form submission
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $client) {
    $action = $_POST['action'] ?? 'send';
    
    if ($action === 'send') {
        $phone = trim($_POST['phone'] ?? '');
        $message = $_POST['message'] ?? '';
        $sender = trim($_POST['sender'] ?? $config['sender']);
        
        if (empty($phone) || empty($message)) {
            $error = 'Phone and message are required';
        } else {
            try {
                $result = $client->sendSms($phone, $message, $sender);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    } elseif ($action === 'bulk') {
        $phones = $_POST['phones'] ?? '';
        $message = $_POST['message'] ?? '';
        $sender = trim($_POST['sender'] ?? $config['sender']);
        
        if (empty($phones) || empty($message)) {
            $error = 'Phones and message are required';
        } else {
            try {
                $phoneList = array_filter(array_map('trim', explode("\n", $phones)));
                $result = $client->sendSms($phoneList, $message, $sender);
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Get balance
$balance = null;
if ($client) {
    try {
        $balanceData = $client->checkBalance();
        $balance = $balanceData['balance'] ?? null;
    } catch (Exception $e) {
        // Silently fail
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Dashboard - AdaReach</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📱</text></svg>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container { 
            max-width: 900px; 
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px 10px 0 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .header h1 {
            color: #333;
            font-size: 24px;
        }
        
        .balance {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .main-content {
            background: white;
            padding: 30px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .config-warning {
            background: #fff3cd;
            color: #856404;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        
        .config-warning h3 {
            margin-bottom: 10px;
        }
        
        .config-warning code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 30px;
        }
        
        .tab {
            padding: 12px 24px;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 16px;
            color: #666;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group { 
            margin-bottom: 20px; 
        }
        
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: 600;
            color: #333;
        }
        
        input, textarea { 
            width: 100%; 
            padding: 12px 15px; 
            border: 2px solid #e0e0e0; 
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color 0.3s;
        }
        
        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea { 
            min-height: 120px; 
            resize: vertical;
        }
        
        .btn { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 14px 32px; 
            border: none; 
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn:hover { 
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px 20px; 
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            animation: slideIn 0.3s;
        }
        
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px 20px; 
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
            animation: slideIn 0.3s;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .char-info {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 13px;
            color: #666;
        }
        
        .unicode-badge {
            background: #e91e63;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .info-box {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
            border-left: 4px solid #2196F3;
        }
        
        .info-box h3 {
            color: #1976D2;
            margin-bottom: 10px;
        }
        
        .info-box ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-box li {
            padding: 5px 0;
            color: #0d47a1;
        }
        
        .info-box li:before {
            content: "✓ ";
            color: #4CAF50;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
        
        .footer {
            text-align: center;
            color: white;
            margin-top: 20px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📱 SMS Dashboard</h1>
            <?php if ($balance !== null): ?>
                <div class="balance">💰 Balance: BDT <?= htmlspecialchars($balance) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="main-content">
            <?php if (!$isConfigured): ?>
                <div class="config-warning">
                    <h3>⚠️ Configuration Required</h3>
                    <p>Please configure your AdaReach SMS credentials:</p>
                    <br>
                    <strong>Option 1: Environment Variables</strong>
                    <pre style="background: #fff; padding: 10px; border-radius: 4px; overflow-x: auto;">
export ADAREARCH_USERNAME="your_username"
export ADAREARCH_PASSWORD="your_password"
export ADAREARCH_SENDER="your_sender_id"
export ADAREARCH_BASE_URL="https://api.mobireach.com.bd"</pre>
                    
                    <strong>Option 2: Create .env file</strong>
                    <pre style="background: #fff; padding: 10px; border-radius: 4px; overflow-x: auto;">
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_SENDER=your_sender_id
ADAREARCH_BASE_URL=https://api.mobireach.com.bd</pre>
                    
                    <strong>Option 3: Edit this file directly (line 52)</strong>
                </div>
            <?php else: ?>
                
                <?php if ($result && isset($result['status']) && $result['status'] === 'SUCCESS'): ?>
                    <div class="success">
                        <strong>✅ SMS sent successfully!</strong><br>
                        <small>Response: <?= htmlspecialchars(json_encode($result)) ?></small>
                    </div>
                <?php elseif ($error): ?>
                    <div class="error">
                        <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <div class="tabs">
                    <button class="tab active" onclick="showTab('send')">Send SMS</button>
                    <button class="tab" onclick="showTab('bulk')">Bulk SMS</button>
                    <button class="tab" onclick="showTab('info')">Info</button>
                </div>
                
                <!-- Send SMS Tab -->
                <div id="send" class="tab-content active">
                    <form method="POST">
                        <input type="hidden" name="action" value="send">
                        <div class="grid">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" placeholder="01703611109 or 8801703611109" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Sender ID</label>
                                <input type="text" name="sender" value="<?= htmlspecialchars($config['sender']) ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" id="message" placeholder="Enter your message... (Bangla supported)" required></textarea>
                            <div class="char-info">
                                <span>
                                    <span id="charCount">0</span> characters • 
                                    <span id="smsCount">0</span> SMS
                                    <span id="unicodeBadge" class="unicode-badge" style="display: none;">UNICODE</span>
                                </span>
                                <span id="limit">Limit: 160</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn">Send SMS</button>
                    </form>
                </div>
                
                <!-- Bulk SMS Tab -->
                <div id="bulk" class="tab-content">
                    <form method="POST">
                        <input type="hidden" name="action" value="bulk">
                        <div class="form-group">
                            <label>Phone Numbers (one per line)</label>
                            <textarea name="phones" placeholder="01712345678&#10;01812345678&#10;01912345678" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" placeholder="Bulk message..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Sender ID</label>
                            <input type="text" name="sender" value="<?= htmlspecialchars($config['sender']) ?>">
                        </div>
                        
                        <button type="submit" class="btn">Send Bulk SMS</button>
                    </form>
                </div>
                
                <!-- Info Tab -->
                <div id="info" class="tab-content">
                    <div class="info-box">
                        <h3>📋 Features</h3>
                        <ul>
                            <li>Auto phone number normalization</li>
                            <li>Bangla/Unicode auto-detection</li>
                            <li>Emoji support</li>
                            <li>Single and Bulk SMS</li>
                        </ul>
                    </div>
                    
                    <div class="info-box" style="margin-top: 20px; background: #fff3cd; border-left-color: #ffc107;">
                        <h3 style="color: #856404;">📏 Character Limits</h3>
                        <ul>
                            <li style="color: #856404;">English: 160 chars/SMS</li>
                            <li style="color: #856404;">Bangla: 70 chars/SMS</li>
                            <li style="color: #856404;">Unicode: 70 chars/SMS</li>
                        </ul>
                    </div>
                </div>
                
            <?php endif; ?>
        </div>
        
        <div class="footer">
            <p>Powered by AdaReach SMS Package • <a href="https://github.com/chandan07cse/robi-sms" style="color: white;">GitHub</a></p>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
        
        const messageInput = document.getElementById('message');
        if (messageInput) {
            const charCount = document.getElementById('charCount');
            const smsCount = document.getElementById('smsCount');
            const limit = document.getElementById('limit');
            const unicodeBadge = document.getElementById('unicodeBadge');
            
            messageInput.addEventListener('input', function() {
                const text = this.value;
                const length = text.length;
                
                const isUnicode = /[^\x00-\x7F]/.test(text);
                const charLimit = isUnicode ? 70 : 160;
                const sms = Math.ceil(length / charLimit) || 0;
                
                charCount.textContent = length;
                smsCount.textContent = sms;
                limit.textContent = 'Limit: ' + charLimit;
                
                if (isUnicode) {
                    unicodeBadge.style.display = 'inline-block';
                    messageInput.style.borderColor = '#e91e63';
                } else {
                    unicodeBadge.style.display = 'none';
                    messageInput.style.borderColor = '#e0e0e0';
                }
            });
        }
    </script>
</body>
</html>
