<?php

namespace Core;

use Core\Database;
use Core\App;

class RateLimit {
    private Database $db;
    private string $clientId;
    private array $limits;
    private string $cacheDir;
    
    public function __construct(?string $clientId = null) {
        $this->db = App::resolve(Database::class);
        $this->clientId = $clientId ?? $this->getClientIdentifier();
        $this->cacheDir = BASE_PATH . 'storage/cache/rate_limits/';
        $this->ensureCacheDirectory();
        
        // Define rate limit tiers
        $this->limits = [
            'default' => [
                'requests' => 100,
                'window' => 3600, // 1 hour
                'burst' => 20     // Allow burst of 20 requests per minute
            ],
            'api' => [
                'requests' => 1000,
                'window' => 3600, // 1 hour
                'burst' => 50     // Allow burst of 50 requests per minute
            ],
            'strict' => [
                'requests' => 50,
                'window' => 3600, // 1 hour
                'burst' => 10     // Allow burst of 10 requests per minute
            ]
        ];
    }
    
    /**
     * Check if request is within rate limits
     */
    public function checkLimit(string $tier = 'default'): array {

        // Clean up expired cache files automatically
        $this->cleanExpiredCache(); 
        
        $limits = $this->limits[$tier] ?? $this->limits['default'];
        
        // Check both hourly and burst limits
        $hourlyCheck = $this->checkWindow($limits['requests'], $limits['window'], 'hourly');
        
        $burstCheck = $this->checkWindow($limits['burst'], 60, 'burst');
        
        // If either limit is exceeded, deny the request
        if (!$hourlyCheck['allowed'] || !$burstCheck['allowed']) {
            return [
                'allowed' => false,
                'limit' => $limits['requests'],
                'remaining' => min($hourlyCheck['remaining'], $burstCheck['remaining']),
                'reset_time' => max($hourlyCheck['reset_time'], $burstCheck['reset_time']),
                'retry_after' => max($hourlyCheck['retry_after'], $burstCheck['retry_after'])
            ];
        }
        
        return [
            'allowed' => true,
            'limit' => $limits['requests'],
            'remaining' => min($hourlyCheck['remaining'], $burstCheck['remaining']),
            'reset_time' => $hourlyCheck['reset_time'],
            'retry_after' => 0
        ];
    }
    
    /**
     * Check rate limit for a specific time window
     */
    private function checkWindow(int $maxRequests, int $windowSeconds, string $type): array {
        $currentTime = time();
        $windowStart = $currentTime - $windowSeconds;
        $cacheKey = $this->getCacheKey($type, $windowSeconds);
        
        // Get current request count from cache
        $data = $this->getFromCache($cacheKey);
        
        if (!$data) {
            $data = [
                'requests' => [],
                'count' => 0
            ];
        }
        
        // Remove expired requests
        $data['requests'] = array_filter($data['requests'], function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });
        
        $currentCount = count($data['requests']);
        
        // Check if limit exceeded
        if ($currentCount >= $maxRequests) {
            $oldestRequest = min($data['requests']);
            $resetTime = $oldestRequest + $windowSeconds;
            
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => $resetTime,
                'retry_after' => $resetTime - $currentTime
            ];
        }
        
        // Add current request
        $data['requests'][] = $currentTime;
        $data['count'] = count($data['requests']);
        
        // Save to cache
        $this->saveToCache($cacheKey, $data, $windowSeconds);
        
        $resetTime = $currentTime + $windowSeconds;
        if (!empty($data['requests'])) {
            $oldestRequest = min($data['requests']);
            $resetTime = $oldestRequest + $windowSeconds;
        }
        
        return [
            'allowed' => true,
            'remaining' => $maxRequests - $data['count'],
            'reset_time' => $resetTime,
            'retry_after' => 0
        ];
    }
    
    /**
     * Get client identifier based on IP and User-Agent
     */
    private function getClientIdentifier(): string {
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        // Create a unique identifier
        return hash('sha256', $ip . '|' . $userAgent);
    }
    
    /**
     * Get real client IP address
     */
    private function getClientIP(): string {
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Generate cache key
     */
    private function getCacheKey(string $type, int $window): string {
        return sprintf('rate_limit_%s_%s_%d', $this->clientId, $type, $window);
    }
    
    /**
     * Get data from file cache
     */
    private function getFromCache(string $key): ?array {
        $file = $this->cacheDir . $key . '.json';
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        // Check if cache has expired
        if (isset($data['expires']) && $data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['data'] ?? null;
    }
    
    /**
     * Save data to file cache
     */
    private function saveToCache(string $key, array $data, int $ttl): void {
        $file = $this->cacheDir . $key . '.json';
        $cacheData = [
            'data' => $data,
            'expires' => time() + $ttl + 60 // Add 60 seconds buffer
        ];
        
        file_put_contents($file, json_encode($cacheData), LOCK_EX);
    }
    
    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectory(): void {
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Clean expired cache files
     */
    public function cleanExpiredCache(): void {
        $files = glob($this->cacheDir . '*.json');
        $currentTime = time();
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if (isset($data['expires']) && $data['expires'] < $currentTime) {
                unlink($file);
            }
        }
    }
    
    /**
     * Get rate limit info for client
     */
    public function getInfo(string $tier = 'default'): array {
        $limits = $this->limits[$tier] ?? $this->limits['default'];
        $result = $this->checkLimit($tier);
        
        return [
            'client_id' => substr($this->clientId, 0, 8) . '...',
            'tier' => $tier,
            'hourly_limit' => $limits['requests'],
            'burst_limit' => $limits['burst'],
            'remaining' => $result['remaining'],
            'reset_time' => $result['reset_time'],
            'reset_time_human' => date('Y-m-d H:i:s', $result['reset_time'])
        ];
    }
    
    /**
     * Check if IP is whitelisted
     */
    public function isWhitelisted(): bool {
        $whitelistedIPs = [
            '127.0.0.1',
            '::1',
            'localhost'
        ];
        
        $clientIP = $this->getClientIP();
        return in_array($clientIP, $whitelistedIPs);
    }
}