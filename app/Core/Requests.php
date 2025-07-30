<?php

namespace Core;
use Core\Validator;

class Requests {

    private static array $data = [];
    private static ?Validator $validator = null;

    public static function initialize(): void
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? "";
        
        $raw = file_get_contents('php://input');

        $input = stripos($contentType, 'application/json') !== false
            ? json_decode($raw, true)
            : $_POST;

        try {
            if(empty($input) || !is_array($input)) {
                throw new \InvalidArgumentException("Input data must be a non-empty array.", 400);
            }
        } catch (\Exception $e) {
            abort(400, [
                "message" => "Invalid input data format.",
                "serverError" => $e
            ]);
        }
        
        self::$data = array_map('h', $input);
    }

    public static function all(): array
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        
        return self::$data;
    }

    public static function validate(array $rules): void
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        
        self::$validator = new Validator(self::$data, $rules);
    }

    public static function fails(): bool
    {
        return !self::$validator?->passes() ?? true;
    }

    public static function errors(): array
    {
        return self::$validator?->errors() ?? [];
    }

    public static function get(string $key, $default = null)
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        
        return self::$data[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        
        return isset(self::$data[$key]);
    }

    public static function only(array $keys): array
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        
        return array_intersect_key(self::$data, array_flip($keys));
    }

    public static function except(array $keys): array
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        
        return array_diff_key(self::$data, array_flip($keys));
    }

    public static function reset(): void
    {
        self::$data = [];
        self::$validator = null;
    }
}