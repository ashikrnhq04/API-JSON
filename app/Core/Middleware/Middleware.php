<?php

namespace Core\Middleware;
use Core\Middleware\Admin;
use Core\Middleware\Guest;
use Core\Middleware\RateLimitMiddleware;

class Middleware {

    public const AUTH = [
        'guest' => Guest::class,
        'admin' => Admin::class,
        'rate_limit' => RateLimitMiddleware::class,
        'rate_limit_strict' => [RateLimitMiddleware::class, 'strict'],
        'rate_limit_api' => [RateLimitMiddleware::class, 'api'],
        'rate_limit_default' => [RateLimitMiddleware::class, 'default']
    ];

    public static function resolve($key) {

        if(!$key) {
            return; 
        }

        $middleware = static::AUTH[$key] ?? false;

        if(!$middleware) {
            throw new \InvalidArgumentException("Middleware not found: {$key}", 500);
        }

        // Handle class with method
        if (is_array($middleware)) {
            $class = $middleware[0];
            $method = $middleware[1];
            call_user_func([$class, $method]);
        } else {
            // Handle regular class
            (new $middleware())->handle();
        }
        
    }
}