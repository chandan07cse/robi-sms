# Using SMS Dashboard in Standalone Projects

## Overview

The SMS Dashboard is built with **Laravel + Vue.js** and requires Laravel's ecosystem. For standalone PHP projects, you have **3 options**:

---

## Option 1: Create a Simple PHP Dashboard (Recommended)

Create your own lightweight dashboard using plain PHP/HTML/JavaScript.

### Quick Setup (15 minutes)

```php
<?php
// dashboard.php - Simple SMS Dashboard

session_start();
require 'vendor/autoload.php';

use Chandan07cse\AdaReach\StandaloneClient;

// Initialize client
$client = new StandaloneClient(
    'your_username',
    'your_password',
    'your_sender_id',
    'https://api.mobireach.com.bd'
);

// Handle form submission
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
    
    try {
        $result = $client->sendSms($phone, $message);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>SMS Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f5f5;
            padding: 20px;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #333; 
            margin-bottom: 30px;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: bold;
            color: #555;
        }
        input, textarea { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            font-size: 14px;
        }
        textarea { 
            min-height: 120px; 
            resize: vertical;
            font-family: inherit;
        }
        button { 
            background: #4CAF50; 
            color: white; 
            padding: 12px 30px; 
            border: none; 
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover { 
            background: #45a049; 
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .char-count {
            text-align: right;
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        .info {
            background: #d1ecf1;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📱 SMS Dashboard</h1>
        
        <?php if ($result && isset($result['status']) && $result['status'] === 'SUCCESS'): ?>
            <div class="success">
                ✅ SMS sent successfully!<br>
                <small>Message ID: <?= $result['id'] ?? 'N/A' ?></small>
            </div>
        <?php elseif ($error): ?>
            <div class="error">
                ❌ Error: <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="01703611109 or 8801703611109" required>
                <small style="color: #666;">Formats: 01XXXXXXXXX or 8801XXXXXXXXX</small>
            </div>
            
            <div class="form-group">
                <label>Message</label>
                <textarea name="message" id="message" placeholder="Enter your message..." required></textarea>
                <div class="char-count">
                    <span id="charCount">0</span> characters 
                    (<span id="smsCount">0</span> SMS)
                </div>
            </div>
            
            <button type="submit">Send SMS</button>
        </form>
        
        <div class="info">
            <strong>ℹ️ Character Limits:</strong><br>
            • English: 160 characters per SMS<br>
            • Bangla/Unicode: 70 characters per SMS<br>
            • Automatic detection for Bangla text
        </div>
    </div>
    
    <script>
        const messageInput = document.getElementById('message');
        const charCount = document.getElementById('charCount');
        const smsCount = document.getElementById('smsCount');
        
        messageInput.addEventListener('input', function() {
            const text = this.value;
            const length = text.length;
            
            // Detect if Unicode (Bangla, Emoji, etc.)
            const isUnicode = /[^\x00-\x7F]/.test(text);
            const limit = isUnicode ? 70 : 160;
            const sms = Math.ceil(length / limit) || 0;
            
            charCount.textContent = length;
            smsCount.textContent = sms;
            
            // Change color based on type
            charCount.parentElement.style.color = isUnicode ? '#e91e63' : '#666';
        });
    </script>
</body>
</html>
```

**Usage:**
```bash
# Place dashboard.php in your project
php -S localhost:8080 dashboard.php

# Open browser
http://localhost:8080
```

---

## Option 2: Use the Laravel Package (Quick Install)

If you can use Laravel (even minimally), install a fresh Laravel instance just for the dashboard:

### Setup (10 minutes)

```bash
# 1. Create Laravel project for dashboard
composer create-project laravel/laravel sms-dashboard
cd sms-dashboard

# 2. Install AdaReach package
composer require chandan07cse/adarearch-laravel

# 3. Configure
php artisan adarearch:install

# 4. Set credentials in .env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_SENDER=your_sender_id
ADAREARCH_BASE_URL=https://api.mobireach.com.bd

# 5. Run
php artisan serve

# 6. Access dashboard
http://localhost:8000/adarearch/dashboard
```

**Then use the standalone client in your main project:**
```php
// Your main standalone project
require 'vendor/autoload.php';

use Chandan07cse\AdaReach\StandaloneClient;

$client = new StandaloneClient(...);
$client->sendSms(...);
```

---

## Option 3: Build API Endpoints + Frontend

Create REST API endpoints in your standalone project, then build a separate frontend.

### Backend (PHP API)

```php
<?php
// api.php - REST API for SMS

require 'vendor/autoload.php';

use Chandan07cse\AdaReach\StandaloneClient;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$client = new StandaloneClient(
    'your_username',
    'your_password',
    'your_sender_id',
    'https://api.mobireach.com.bd'
);

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'send':
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $client->sendSms(
                $data['phone'],
                $data['message']
            );
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        case 'balance':
            $result = $client->checkBalance();
            echo json_encode(['success' => true, 'data' => $result]);
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

### Frontend (HTML + JavaScript)

```html
<!DOCTYPE html>
<html>
<head>
    <title>SMS Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <style>
        /* Add your styles */
    </style>
</head>
<body>
    <div id="app">
        <h1>SMS Dashboard</h1>
        
        <div v-if="message" :class="messageClass">{{ message }}</div>
        
        <form @submit.prevent="sendSms">
            <input v-model="phone" placeholder="Phone number" required>
            <textarea v-model="smsMessage" placeholder="Message" required></textarea>
            <button type="submit" :disabled="loading">
                {{ loading ? 'Sending...' : 'Send SMS' }}
            </button>
        </form>
    </div>
    
    <script>
        const { createApp } = Vue;
        
        createApp({
            data() {
                return {
                    phone: '',
                    smsMessage: '',
                    message: '',
                    messageClass: '',
                    loading: false
                };
            },
            methods: {
                async sendSms() {
                    this.loading = true;
                    this.message = '';
                    
                    try {
                        const response = await axios.post('api.php?action=send', {
                            phone: this.phone,
                            message: this.smsMessage
                        });
                        
                        this.message = 'SMS sent successfully!';
                        this.messageClass = 'success';
                        this.phone = '';
                        this.smsMessage = '';
                    } catch (error) {
                        this.message = 'Error: ' + error.response.data.error;
                        this.messageClass = 'error';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }).mount('#app');
    </script>
</body>
</html>
```

---

## Feature Comparison

| Feature | Option 1 (Simple PHP) | Option 2 (Laravel) | Option 3 (API + Vue) |
|---------|----------------------|-------------------|---------------------|
| Setup Time | 15 min | 10 min | 30 min |
| Complexity | Low | Medium | Medium |
| Features | Basic | Full Dashboard | Custom |
| Dependencies | None | Laravel | API Server |
| Best For | Quick & Simple | Full Features | Custom UI |

---

## Recommended Approach

### For Most Projects: **Option 1** (Simple PHP Dashboard)

✅ **Advantages:**
- No framework required
- Works with any PHP project
- Easy to customize
- Minimal dependencies
- Quick setup

### If You Need Full Features: **Option 2** (Laravel Package)

✅ **Advantages:**
- Complete dashboard with all features
- Message history
- Statistics
- Settings management
- Pre-built UI

### For Custom Requirements: **Option 3** (API + Custom Frontend)

✅ **Advantages:**
- Full control over UI/UX
- Can integrate with existing frontend
- Reusable API
- Framework-agnostic

---

## Quick Start (Option 1 - Recommended)

1. **Create `dashboard.php`** (copy the code above)

2. **Update credentials:**
```php
$client = new StandaloneClient(
    'khulnauni',        // Your username
    'Khulna@1991',      // Your password
    '01810187701',      // Your sender
    'https://api.mobireach.com.bd'
);
```

3. **Run:**
```bash
php -S localhost:8080 dashboard.php
```

4. **Open:** http://localhost:8080

That's it! You now have a working SMS dashboard in your standalone project. 🎉

---

## Additional Features You Can Add

### 1. Authentication
```php
// Add at top of dashboard.php
if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}
```

### 2. Message History (Database)
```php
// Store sent messages
$pdo->prepare("INSERT INTO sms_log (phone, message, status) VALUES (?, ?, ?)")
    ->execute([$phone, $message, $result['status']]);
```

### 3. Bulk SMS Upload
```php
// Handle CSV upload
if (isset($_FILES['csv'])) {
    $csv = array_map('str_getcsv', file($_FILES['csv']['tmp_name']));
    foreach ($csv as $row) {
        $client->sendSms($row[0], $row[1]);
    }
}
```

### 4. Scheduled SMS
```php
// Use cron + database
$scheduled = $pdo->query("SELECT * FROM scheduled_sms WHERE send_at <= NOW()")->fetchAll();
foreach ($scheduled as $sms) {
    $client->sendSms($sms['phone'], $sms['message']);
}
```

---

## Summary

✅ **Best for Standalone Projects:** Simple PHP dashboard (Option 1)  
✅ **Setup Time:** 15 minutes  
✅ **Dependencies:** Just the standalone client  
✅ **Features:** Send SMS, auto-detect Bangla, character counter  
✅ **Customizable:** Easy to modify HTML/CSS/JS  

**The Laravel dashboard is designed for Laravel projects, but you can easily create your own lightweight dashboard for standalone PHP projects!** 🚀
