<?php
namespace Core;

use Core\Validator;
/**
 * Class Requests
 *
 * Handles HTTP request data, input validation, and provides access to request parameters.
 */
class Requests {

    /**
     * Stores sanitized request data.
     * @var array
     */
    private static array $data = [];

    /**
     * Validator instance for request data.
     * @var Validator|null
     */
    private static ?Validator $validator = null;

    /**
     * Initialize and sanitize input data from request.
     */
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
            self::$data = array_map('h', $input);
        } catch (\Exception $e) {
            abort(400, [
                "message" => "Invalid input data format. " . $e->getMessage(),
                "serverError" => $e
            ]);
        }
    }

    /**
     * Get all request data.
     * @return array
     */
    public static function all(): array
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        return self::$data;
    }

    /**
     * Validate request data against rules.
     * @param array $rules
     */
    public static function validate(array $rules): void
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        self::$validator = new Validator(self::$data, $rules);
    }

    /**
     * Check if validation fails.
     * @return bool
     */
    public static function fails(): bool
    {
        return !self::$validator?->passes() ?? true;
    }

    /**
     * Get validation errors.
     * @return array
     */
    public static function errors(): array
    {
        return self::$validator?->errors() ?? [];
    }

    /**
     * Get a specific request parameter.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (empty(self::$data)) {
            self::initialize();
        }
        return self::$data[$key] ?? $default;
    }

    /**
     * Check if a request parameter exists.
     * @param string $key
     * @return bool
     */
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