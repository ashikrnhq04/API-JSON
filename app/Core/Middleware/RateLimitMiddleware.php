<?php

namespace Core\Middleware;

use Core\RateLimit;
use Views\JsonView;

class RateLimitMiddleware {
    
    /**
     * Handle rate limiting for API requests
     */
    public static function handle(string $tier = 'api'): void {
        $rateLimit = new RateLimit();
        
        // Skip rate limiting for whitelisted IPs
        if ($rateLimit->isWhitelisted()) {
            // Still add headers for whitelisted IPs to show they're not rate limited
            header('X-RateLimit-Limit: unlimited');
            header('X-RateLimit-Remaining: unlimited');
            header('X-RateLimit-Reset: 0');
            return;
        }
        
        $result = $rateLimit->checkLimit($tier);
        
        // Add rate limit headers
        self::addRateLimitHeaders($result);
        
        // If rate limit exceeded, return error
        if (!$result['allowed']) {
            http_response_code(429);
            JsonView::rateLimited(
                'Rate limit exceeded. Please try again later.',
                $result
            );
            exit;
        }
    }
    
    /**
     * Add rate limit headers to response
     */
    private static function addRateLimitHeaders(array $result): void {
        header('X-RateLimit-Limit: ' . $result['limit']);
        header('X-RateLimit-Remaining: ' . $result['remaining']);
        header('X-RateLimit-Reset: ' . $result['reset_time']);
        
        if (!$result['allowed']) {
            header('Retry-After: ' . $result['retry_after']);
        }
    }
    
    /**
     * Handle strict rate limiting
     */
    public static function strict(): void {
        self::handle('strict');
    }
    
    /**
     * Handle default rate limiting
     */
    public static function default(): void {
        self::handle('default');
    }
    
    /**
     * Handle API rate limiting
     */
    public static function api(): void {
        self::handle('api');
    }
}
