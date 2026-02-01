<?php
/**
 * Simple SMS Dashboard for Standalone Projects
 * 
 * Requirements: PHP 7.4+, AdaReach SMS Package
 * Usage: php -S localhost:8080 dashboard.php
 */

session_start();
require __DIR__ . '/vendor/autoload.php';

use AdaReach\Sms\StandaloneClient;

// ============================================
// CONFIGURATION - Update with your credentials
// ============================================
$config = [
    'username' => 'your_username',
    'password' => 'your_password',
    'sender' => 'your_sender_id',
    'base_url' => 'https://api.mobireach.com.bd'
];

// Initialize client
$client = new StandaloneClient(
    $config['username'],
    $config['password'],
    $config['sender'],
    $config['base_url']
);

// Handle form submission
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $message = $_POST['message'] ?? '';
    $sender = trim($_POST['sender'] ?? $config['sender']);
    
    if (empty($phone) || empty($message)) {
        $error = 'Phone and message are required';
    } else {
        try {
            $result = $client->sendSms($phone, $message, $sender);
            // Store in session for display
            $_SESSION['last_result'] = $result;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Get balance
$balance = null;
try {
    $balanceData = $client->checkBalance();
    $balance = $balanceData['balance'] ?? 'N/A';
} catch (Exception $e) {
    // Silently fail for balance check
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Dashboard - AdaReach</title>
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
            
            .header {
                flex-direction: column;
                gap: 15px;
            }
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
            <?php if ($result && isset($result['status']) && $result['status'] === 'SUCCESS'): ?>
                <div class="success">
                    <strong>✅ SMS sent successfully!</strong><br>
                    <small>Message ID: <?= htmlspecialchars($result['id'] ?? 'N/A') ?></small>
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
                    <h3>📋 Supported Features</h3>
                    <ul>
                        <li>Auto phone number normalization (01... or 880...)</li>
                        <li>Bangla/Unicode SMS with auto-detection</li>
                        <li>Emoji support (🎉 😊 ❤️)</li>
                        <li>Mixed language (Bangla + English)</li>
                        <li>Single and Bulk SMS</li>
                    </ul>
                </div>
                
                <div class="info-box" style="margin-top: 20px; background: #fff3cd; border-left-color: #ffc107;">
                    <h3 style="color: #856404;">📏 Character Limits</h3>
                    <ul>
                        <li style="color: #856404;">English: 160 characters per SMS</li>
                        <li style="color: #856404;">Bangla: 70 characters per SMS</li>
                        <li style="color: #856404;">Unicode/Emoji: 70 characters per SMS</li>
                    </ul>
                </div>
                
                <div class="info-box" style="margin-top: 20px; background: #f3e5f5; border-left-color: #9c27b0;">
                    <h3 style="color: #6a1b9a;">🔧 Configuration</h3>
                    <ul>
                        <li style="color: #6a1b9a;">Username: <?= htmlspecialchars($config['username']) ?></li>
                        <li style="color: #6a1b9a;">Default Sender: <?= htmlspecialchars($config['sender']) ?></li>
                        <li style="color: #6a1b9a;">API URL: <?= htmlspecialchars($config['base_url']) ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching
        function showTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
        
        // Character counter
        const messageInput = document.getElementById('message');
        const charCount = document.getElementById('charCount');
        const smsCount = document.getElementById('smsCount');
        const limit = document.getElementById('limit');
        const unicodeBadge = document.getElementById('unicodeBadge');
        
        messageInput.addEventListener('input', function() {
            const text = this.value;
            const length = text.length;
            
            // Detect if Unicode (Bangla, Emoji, etc.)
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
    </script>
</body>
</html>
