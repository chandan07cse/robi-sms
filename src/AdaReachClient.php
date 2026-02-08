<?php

namespace AdaReach\Sms;

use AdaReach\Sms\Exceptions\AdaReachException;
use AdaReach\Sms\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AdaReachClient
{
    protected string $username;
    protected string $password;
    protected string $baseUrl;
    protected ?string $token = null;
    protected ?string $refreshToken = null;

    public function __construct(string $username = null, string $password = null, string $baseUrl = null)
    {
        // Try to load from database settings first, fallback to config
        $this->username = $username ?? Setting::get('api_username') ?? config('adarearch.username');
        $this->password = $password ?? Setting::get('api_password') ?? config('adarearch.password');
        $this->baseUrl = rtrim($baseUrl ?? Setting::get('api_base_url') ?? config('adarearch.base_url'), '/');
        
        $this->loadTokensFromCache();
    }

    /**
     * Generate a new access token
     */
    public function generateToken(): array
    {
        $response = Http::post("{$this->baseUrl}/auth/tokens", [
            'username' => $this->username,
            'password' => $this->password,
        ]);

        if ($response->failed()) {
            $errorMessage = $response->json('message') 
                ?? $response->json('description') 
                ?? 'Failed to generate token';
                
            throw new AdaReachException(
                $errorMessage,
                $response->json('errorCode', $response->status())
            );
        }

        $data = $response->json();
        $this->token = $data['token'];
        $this->refreshToken = $data['refresh_token'];
        
        $this->cacheTokens();
        
        return $data;
    }

    /**
     * Refresh the access token
     */
    public function refreshToken(): array
    {
        if (!$this->refreshToken) {
            return $this->generateToken();
        }

        $response = Http::withToken($this->refreshToken)
            ->post("{$this->baseUrl}/auth/token/refresh");

        if ($response->failed()) {
            // If refresh fails, generate new token
            return $this->generateToken();
        }

        $data = $response->json();
        $this->token = $data['token'];
        $this->refreshToken = $data['refresh_token'];
        
        $this->cacheTokens();
        
        return $data;
    }

    /**
     * Send SMS (single or bulk)
     * Note: Does NOT retry on authentication errors to prevent duplicate SMS sends.
     * Relies on proactive token refresh to prevent auth errors.
     */
    public function sendSms(array $params): array
    {
        $this->ensureValidToken();

        $startTime = microtime(true);
        
        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/sms/send", $params);

        $responseTime = microtime(true) - $startTime;
        // Check for authentication error (HTTP 401 or errorCode 1501)
        // Do NOT retry to prevent duplicate SMS sends
        if ($this->isAuthenticationError($response)) {
            throw new AdaReachException(
                'Authentication failed. Token may be expired.',
                1501
            );
        }

        if ($response->failed()) {
            throw new AdaReachException(
                $response->json('description', 'Failed to send SMS'),
                $response->json('errorCode', $response->status())
            );
        }

        $result = $response->json();
        $result['response_time'] = round($responseTime, 3);
        
        return $result;
    }

    /**
     * Check message status (DLR)
     */
    public function checkStatus(string $sender, string $messageId, string $receiver): array
    {
        $this->ensureValidToken();

        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/sms/status", [
                'sender' => $sender,
                'messageId' => $messageId,
                'receiver' => $receiver,
            ]);

        // If authentication error (HTTP 401 or errorCode 1501), refresh and retry once
        if ($this->isAuthenticationError($response)) {
            $this->clearTokenCache();
            $this->refreshToken();
            
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/sms/status", [
                    'sender' => $sender,
                    'messageId' => $messageId,
                    'receiver' => $receiver,
                ]);
        }

        if ($response->failed()) {
            throw new AdaReachException(
                $response->json('description', 'Failed to check status'),
                $response->json('errorCode', $response->status())
            );
        }

        return $response->json();
    }

    /**
     * Check account balance
     */
    public function checkBalance(): array
    {
        $this->ensureValidToken();

        $response = Http::withToken($this->token)
            ->get("{$this->baseUrl}/balance", [
                'username' => $this->username,
            ]);

        // If authentication error (HTTP 401 or errorCode 1501), refresh and retry once
        if ($this->isAuthenticationError($response)) {
            $this->clearTokenCache();
            $this->refreshToken();
            
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/balance", [
                    'username' => $this->username,
                ]);
        }

        if ($response->failed()) {
            $errorMessage = $response->json('message') 
                ?? $response->json('description') 
                ?? 'Failed to check balance';
                
            throw new AdaReachException(
                $errorMessage,
                $response->json('errorCode', $response->status())
            );
        }

        return $response->json();
    }

    /**
     * Ensure we have a valid token
     * Validates token by making a balance check call to verify it's actually working
     */
    protected function ensureValidToken(): void
    {
        if (!$this->token) {
            $this->generateToken();
            return;
        }

        // Check if token is expired or about to expire (within 5 minutes)
        $cacheKey = "adarearch_tokens_{$this->username}";
        $tokenData = Cache::get($cacheKey);
        
        if (!$tokenData || !isset($tokenData['expires_at'])) {
            // No expiration data, try to refresh or generate new token
            if ($this->refreshToken) {
                $this->refreshToken();
            } else {
                $this->generateToken();
            }
            return;
        }

        // If token expires in less than 5 minutes, refresh it
        if (now()->addMinutes(5)->greaterThan($tokenData['expires_at'])) {
            if ($this->refreshToken) {
                $this->refreshToken();
            } else {
                $this->generateToken();
            }
            return;
        }

        // Validate token via balance check (lightweight API call)
        if (!$this->validateTokenViaBalanceCheck()) {
            $this->clearTokenCache();
            if ($this->refreshToken) {
                $this->refreshToken();
            } else {
                $this->generateToken();
            }
        }
    }

    /**
     * Validate token by making a balance check call
     * This ensures the token is actually valid on the API server
     *
     * @return bool True if token is valid
     */
    protected function validateTokenViaBalanceCheck(): bool
    {
        try {
            $response = Http::withToken($this->token)
                ->get("{$this->baseUrl}/balance", [
                    'username' => $this->username,
                ]);

            // Token is invalid if we get auth error
            if ($this->isAuthenticationError($response)) {
                return false;
            }

            return !$response->failed();
        } catch (\Exception $e) {
            // On network error, assume token is still valid to avoid unnecessary re-auth
            return true;
        }
    }

    /**
     * Cache tokens for reuse
     */
    protected function cacheTokens(): void
    {
        $cacheKey = "adarearch_tokens_{$this->username}";
        $expiresAt = now()->addMinutes(55); // Token valid for 1 hour, cache for 55 minutes
        
        Cache::put($cacheKey, [
            'token' => $this->token,
            'refresh_token' => $this->refreshToken,
            'expires_at' => $expiresAt,
            'cached_at' => now(),
        ], $expiresAt);
    }

    /**
     * Check if the response indicates an authentication error
     * API returns HTTP 200 with errorCode: 1501 for invalid tokens
     */
    protected function isAuthenticationError($response): bool
    {
        // Check HTTP status code (401 Unauthorized)
        if ($response->status() === 401) {
            return true;
        }

        // Check errorCode in response body
        // errorCode: 1501 = Invalid/expired token
        $errorCode = $response->json('errorCode');
        if ($errorCode === 1501) {
            return true;
        }

        return false;
    }

    /**
     * Load tokens from cache
     */
    protected function loadTokensFromCache(): void
    {
        $cacheKey = "adarearch_tokens_{$this->username}";
        $tokens = Cache::get($cacheKey);

        if ($tokens) {
            $this->token = $tokens['token'] ?? null;
            $this->refreshToken = $tokens['refresh_token'] ?? null;
        }
    }

    /**
     * Clear cached tokens
     */
    public function clearTokenCache(): void
    {
        $cacheKey = "adarearch_tokens_{$this->username}";
        Cache::forget($cacheKey);
        $this->token = null;
        $this->refreshToken = null;
    }
}
