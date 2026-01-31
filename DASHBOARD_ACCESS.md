# Accessing SMS Dashboard in Standalone Projects

After installing the package via Composer, you can access a ready-to-use SMS dashboard at `/sms-dashboard` URL.

## Quick Setup (2 minutes)

### Option 1: Using .htaccess (Apache)

Create or edit `.htaccess` in your project root:

```apache
# SMS Dashboard Route
RewriteEngine On
RewriteRule ^sms-dashboard$ vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php [L]
```

**Access:** `http://yoursite.com/sms-dashboard`

### Option 2: Using Nginx

Add to your nginx config:

```nginx
location /sms-dashboard {
    rewrite ^/sms-dashboard$ /vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php last;
}
```

**Access:** `http://yoursite.com/sms-dashboard`

### Option 3: Copy to Public Directory

```bash
# Copy dashboard to your public directory
cp vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php public/

# Access directly
http://yoursite.com/sms-dashboard.php
# Or create a route in your framework
```

### Option 4: PHP Built-in Server (Development)

```bash
# Navigate to vendor directory
cd vendor/chandan07cse/adarearch-laravel/public

# Run PHP server
php -S localhost:8095 sms-dashboard.php

# Access
http://localhost:8095
```

---

## Configuration

The dashboard auto-detects configuration from three sources:

### Method 1: Environment Variables (Recommended)

```bash
export ADAREARCH_USERNAME="your_username"
export ADAREARCH_PASSWORD="your_password"
export ADAREARCH_SENDER="your_sender_id"
export ADAREARCH_BASE_URL="https://api.mobireach.com.bd"
```

### Method 2: .env File

Create `.env` in your project root:

```env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_SENDER=your_sender_id
ADAREARCH_BASE_URL=https://api.mobireach.com.bd
```

### Method 3: Config File

Create `config/adarearch.php`:

```php
<?php
return [
    'api_username' => 'your_username',
    'api_password' => 'your_password',
    'default_sender' => 'your_sender_id',
    'api_base_url' => 'https://api.mobireach.com.bd',
];
```

---

## Complete Examples

### Example 1: Plain PHP Project

```bash
# Install package
composer require chandan07cse/adarearch-laravel

# Create .env
cat > .env << EOF
ADAREARCH_USERNAME=khulnauni
ADAREARCH_PASSWORD=Khulna@1991
ADAREARCH_SENDER=01810187701
ADAREARCH_BASE_URL=https://api.mobireach.com.bd
EOF

# Create .htaccess
cat > .htaccess << EOF
RewriteEngine On
RewriteRule ^sms-dashboard$ vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php [L]
EOF

# Access
http://localhost/sms-dashboard
```

### Example 2: Symfony Project

```bash
# Install package
composer require chandan07cse/adarearch-laravel

# Add to .env
echo "ADAREARCH_USERNAME=your_username" >> .env
echo "ADAREARCH_PASSWORD=your_password" >> .env
echo "ADAREARCH_SENDER=your_sender" >> .env

# Create route in config/routes.yaml
cat >> config/routes.yaml << EOF
sms_dashboard:
    path: /sms-dashboard
    controller: Symfony\Bundle\FrameworkBundle\Controller\TemplateController
    defaults:
        template: '@!vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php'
EOF
```

Or simpler - copy to public:
```bash
cp vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php public/
# Access: http://localhost/sms-dashboard.php
```

### Example 3: CodeIgniter 4

```bash
# Install package
composer require chandan07cse/adarearch-laravel

# Add to .env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_SENDER=your_sender
ADAREARCH_BASE_URL=https://api.mobireach.com.bd

# Create route in app/Config/Routes.php
$routes->get('sms-dashboard', function() {
    include ROOTPATH . 'vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php';
});
```

### Example 4: Slim Framework

```bash
# Install package
composer require chandan07cse/adarearch-laravel

# Add to .env
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_SENDER=your_sender

# Add route
$app->get('/sms-dashboard', function ($request, $response) {
    ob_start();
    include __DIR__ . '/../vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php';
    $html = ob_get_clean();
    $response->getBody()->write($html);
    return $response;
});
```

### Example 5: No Framework (Apache)

```bash
# Install
composer require chandan07cse/adarearch-laravel

# Create public/.htaccess
RewriteEngine On
RewriteRule ^sms-dashboard$ ../vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php [L]

# Set env in Apache config or .htaccess
SetEnv ADAREARCH_USERNAME "your_username"
SetEnv ADAREARCH_PASSWORD "your_password"
SetEnv ADAREARCH_SENDER "your_sender"

# Access
http://localhost/sms-dashboard
```

---

## Features

The `/sms-dashboard` endpoint provides:

✅ **Send Single SMS** - Send SMS to one number  
✅ **Bulk SMS** - Send to multiple numbers (one per line)  
✅ **Auto-Detection** - Automatically detects Bangla/Unicode  
✅ **Character Counter** - Shows SMS count and character limit  
✅ **Balance Display** - Shows account balance  
✅ **Beautiful UI** - Modern, responsive design  
✅ **Zero Config** - Auto-detects credentials from env/config  

---

## Security Considerations

### 1. Add Authentication

Protect the dashboard with basic authentication:

```php
// Add at the top of sms-dashboard.php after session_start()
if (!isset($_SESSION['authenticated'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="SMS Dashboard"');
    
    if (!isset($_SERVER['PHP_AUTH_USER']) || 
        $_SERVER['PHP_AUTH_USER'] !== 'admin' || 
        $_SERVER['PHP_AUTH_PW'] !== 'your_password') {
        die('Unauthorized');
    }
    
    $_SESSION['authenticated'] = true;
}
```

### 2. IP Whitelist

Restrict access to specific IPs:

```php
// Add at the top
$allowed_ips = ['127.0.0.1', '192.168.1.100'];
if (!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die('Access denied');
}
```

### 3. Use Environment Variables

Never hardcode credentials - always use environment variables or config files.

---

## Troubleshooting

### Dashboard shows "Configuration Required"

**Solution:** Set environment variables or create .env file with credentials.

### Can't access /sms-dashboard

**Solution:** 
- Check your web server configuration
- Verify .htaccess rewrite rules (Apache)
- Or copy file to public directory directly

### autoload.php not found

**Solution:** Run `composer install` in your project root.

### Permission denied

**Solution:** 
```bash
chmod +x vendor/chandan07cse/adarearch-laravel/public/sms-dashboard.php
chmod -R 755 vendor/chandan07cse/adarearch-laravel/public/
```

---

## Summary

| Setup Method | Difficulty | Best For |
|--------------|-----------|----------|
| Apache .htaccess | Easy | Apache servers |
| Nginx config | Easy | Nginx servers |
| Copy to public | Easiest | Any setup |
| Framework route | Medium | Framework projects |

**Recommended:** 
1. Copy `sms-dashboard.php` to your public directory
2. Create `.env` file with credentials
3. Access at `http://yoursite.com/sms-dashboard.php`

**That's it! The dashboard is ready to use in any standalone PHP project.** 🚀

---

## Quick Reference

```bash
# Installation
composer require chandan07cse/adarearch-laravel

# Configuration (.env)
ADAREARCH_USERNAME=your_username
ADAREARCH_PASSWORD=your_password
ADAREARCH_SENDER=your_sender
ADAREARCH_BASE_URL=https://api.mobireach.com.bd

# Access
http://localhost/sms-dashboard
```

**Package comes with dashboard built-in - no additional setup files needed!**
