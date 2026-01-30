<?php

namespace AdaReach\Sms;

use AdaReach\Sms\Exceptions\AdaReachException;

/**
 * Standalone AdaReach SMS Client for PHP 7.4+
 * Works without Laravel dependencies
 */
class StandaloneClient
{
    protected $username;
    protected $password;
    protected $baseUrl;
    protected $token = null;
    protected $refreshToken = null;
    protected $cacheDir = null;

    public function __construct($username, $password, $baseUrl = 'https://api.mobireach.com.bd/api', $cacheDir = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir();
        
        $this->loadTokensFromCache();
    }

    /**
     * Generate a new access token
     */
    public function generateToken()
    {
        $response = $this->makeRequest('POST', '/auth/tokens', [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if (isset($response['error'])) {
            throw new AdaReachException(
                $response['message'] ?? $response['description'] ?? 'Failed to generate token',
                $response['errorCode'] ?? 500
            );
        }

        $this->token = $response['token'];
        $this->refreshToken = $response['refresh_token'];
        
        $this->cacheTokens();
        
        return $response;
    }

    /**
     * Refresh the access token
     */
    public function refreshToken()
    {
        if (!$this->refreshToken) {
            return $this->generateToken();
        }

        $response = $this->makeRequest('POST', '/auth/token/refresh', [], [
            'Authorization: Bearer ' . $this->refreshToken
        ]);

        if (isset($response['error'])) {
            // If refresh fails, generate new token
            return $this->generateToken();
        }

        $this->token = $response['token'];
        $this->refreshToken = $response['refresh_token'];
        
        $this->cacheTokens();
        
        return $response;
    }

    /**
     * Send SMS (single or bulk)
     */
    public function sendSms($recipients, $message, $sender = null, $campaignId = null)
    {
        $this->ensureValidToken();

        // Normalize recipients to array
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $params = [
            'recipients' => $recipients,
            'text' => $message,
        ];

        if ($sender) {
            $params['sender'] = $sender;
        }

        if ($campaignId) {
            $params['campaignId'] = $campaignId;
        }

        $response = $this->makeRequest('POST', '/sms/send', $params, [
            'Authorization: Bearer ' . $this->token
        ]);

        if (isset($response['error'])) {
            throw new AdaReachException(
                $response['description'] ?? 'Failed to send SMS',
                $response['errorCode'] ?? 500
            );
        }

        return $response;
    }

    /**
     * Check account balance
     */
    public function checkBalance()
    {
        $this->ensureValidToken();

        $response = $this->makeRequest('GET', '/balance', null, [
            'Authorization: Bearer ' . $this->token
        ]);

        if (isset($response['error'])) {
            throw new AdaReachException(
                $response['message'] ?? $response['description'] ?? 'Failed to check balance',
                $response['errorCode'] ?? 500
            );
        }

        return $response;
    }

    /**
     * Get delivery status
     */
    public function getDeliveryStatus($messageId)
    {
        $this->ensureValidToken();

        $response = $this->makeRequest('GET', "/sms/status/{$messageId}", null, [
            'Authorization: Bearer ' . $this->token
        ]);

        if (isset($response['error'])) {
            throw new AdaReachException(
                $response['description'] ?? 'Failed to get status',
                $response['errorCode'] ?? 500
            );
        }

        return $response;
    }

    /**
     * Ensure we have a valid token
     */
    protected function ensureValidToken()
    {
        if (!$this->token) {
            $this->generateToken();
        }
    }

    /**
     * Make HTTP request using cURL
     */
    protected function makeRequest($method, $endpoint, $data = null, $headers = [])
    {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        
        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        
        $headers = array_merge($defaultHeaders, $headers);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'GET' && $data) {
            $url .= '?' . http_build_query($data);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return [
                'error' => true,
                'message' => $error,
                'errorCode' => 0
            ];
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $result['error'] = true;
            $result['errorCode'] = $result['errorCode'] ?? $httpCode;
        }
        
        return $result;
    }

    /**
     * Cache tokens to file
     */
    protected function cacheTokens()
    {
        $cacheFile = $this->getCacheFilePath();
        $data = [
            'token' => $this->token,
            'refresh_token' => $this->refreshToken,
            'expires_at' => time() + 3600, // 1 hour
        ];
        
        file_put_contents($cacheFile, json_encode($data));
    }

    /**
     * Load tokens from cache
     */
    protected function loadTokensFromCache()
    {
        $cacheFile = $this->getCacheFilePath();
        
        if (!file_exists($cacheFile)) {
            return;
        }
        
        $data = json_decode(file_get_contents($cacheFile), true);
        
        if (!$data || $data['expires_at'] < time()) {
            return;
        }
        
        $this->token = $data['token'];
        $this->refreshToken = $data['refresh_token'];
    }

    /**
     * Get cache file path
     */
    protected function getCacheFilePath()
    {
        return $this->cacheDir . '/adarearch_' . md5($this->username) . '.json';
    }

    /**
     * Clear cached tokens
     */
    public function clearCache()
    {
        $cacheFile = $this->getCacheFilePath();
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
        
        $this->token = null;
        $this->refreshToken = null;
    }
}
