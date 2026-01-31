<?php
/**
 * SMS Dashboard Route
 * 
 * Include this file in your application's routing to enable /sms-dashboard
 * 
 * Usage:
 * require __DIR__ . '/vendor/chandan07cse/robi-sms/routes/sms-dashboard.php';
 */

// Check if we're being accessed via /sms-dashboard
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$requestPath = parse_url($requestUri, PHP_URL_PATH);

if ($requestPath === '/sms-dashboard' || preg_match('#/sms-dashboard/?$#', $requestPath)) {
    // Load the dashboard
    require __DIR__ . '/../public/sms-dashboard.php';
    exit;
}
