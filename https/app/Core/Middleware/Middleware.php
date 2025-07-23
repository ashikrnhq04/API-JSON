<?php

namespace Core\Middleware;
use Core\Middleware\Admin;
use Core\Middleware\Guest;


class Middleware {

    public const AUTH = [
        'guest' => Guest::class,
        'admin' => Admin::class
    ];

    public static function resolve($key) {

        if(!$key) {
            return; 
        }

        $middleware = static::AUTH[$key] ?? false;

        if(!$middleware) {
            throw new \InvalidArgumentException("Middleware not found: {$key}", 500);
        }

        (new $middleware())->handle();
        
    }
}