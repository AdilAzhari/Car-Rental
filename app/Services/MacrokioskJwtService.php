<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MacrokioskJwtService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $apiKey;
    private string $tokenEndpoint;
    private string $cacheKey = 'macrokiosk_jwt_token';

    public function __construct()
    {
        $config = config('sms.macrokiosk');

        $this->baseUrl = $config['base_url'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->apiKey = $config['api_key'];
        $this->tokenEndpoint = $config['token_endpoint'];
    }

    /**
     * Get JWT token (from cache or generate new one)
     */
    public function getToken(): ?string
    {
        // Try to get cached token
        $token = Cache::get($this->cacheKey);

        if ($token) {
            Log::debug('Using cached JWT token');
            return $token;
        }

        // Generate new token
        return $this->generateToken();
    }

    /**
     * Generate new JWT token from MACROKIOSK API
     */
    public function generateToken(): ?string
    {
        try {
            $url = $this->baseUrl . $this->tokenEndpoint;

            Log::info('Requesting JWT token from MACROKIOSK', [
                'url' => $url,
                'username' => $this->username,
            ]);

            // Create JWT payload
            $header = [
                'alg' => 'HS256',
                'typ' => 'JWT',
            ];

            $payload = [
                'username' => $this->username,
                'password' => $this->password,
                'iat' => time(), // Issued at
                'exp' => time() + 3600, // Expires in 1 hour
            ];

            // Encode header and payload
            $headerEncoded = $this->base64UrlEncode(json_encode($header));
            $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

            // Create signature
            $signature = $this->createSignature($headerEncoded . '.' . $payloadEncoded);

            // Complete JWT token
            $jwt = $headerEncoded . '.' . $payloadEncoded . '.' . $signature;

            // Request token from API
            $response = Http::timeout(30)
                ->asForm()
                ->post($url, [
                    'username' => $this->username,
                    'password' => $this->password,
                    'token' => $jwt,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['token'])) {
                    $token = $data['token'];

                    // Cache token for 50 minutes (expires in 60, refresh before expiry)
                    Cache::put($this->cacheKey, $token, now()->addMinutes(50));

                    Log::info('JWT token generated successfully');

                    return $token;
                }

                Log::error('JWT token not found in response', ['response' => $data]);
                return null;
            }

            Log::error('JWT token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('JWT token generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Create HMAC-SHA256 signature
     */
    private function createSignature(string $data): string
    {
        $signature = hash_hmac('sha256', $data, $this->apiKey, true);
        return $this->base64UrlEncode($signature);
    }

    /**
     * Base64 URL encode (JWT standard)
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Invalidate cached token (force regeneration)
     */
    public function invalidateToken(): void
    {
        Cache::forget($this->cacheKey);
        Log::info('JWT token cache invalidated');

    }

    /**
     * Check if token is cached
     */
    public function hasValidToken(): bool
    {
        return Cache::has($this->cacheKey);
    }
}
