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
     */
    public function sendSms(array $params): array
    {
        $this->ensureValidToken();

        $response = Http::withToken($this->token)
            ->post("{$this->baseUrl}/sms/send", $params);

        if ($response->failed()) {
            throw new AdaReachException(
                $response->json('description', 'Failed to send SMS'),
                $response->json('errorCode', $response->status())
            );
        }

        return $response->json();
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
     */
    protected function ensureValidToken(): void
    {
        if (!$this->token) {
            $this->generateToken();
        }
    }

    /**
     * Cache tokens for reuse
     */
    protected function cacheTokens(): void
    {
        $cacheKey = "adarearch_tokens_{$this->username}";
        Cache::put($cacheKey, [
            'token' => $this->token,
            'refresh_token' => $this->refreshToken,
        ], now()->addMinutes(55)); // Cache for 55 minutes (token valid for 1 hour)
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
