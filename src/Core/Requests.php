<?php

namespace src\Core;

class Requests {

    private $data; 
    private $error = []; 

    public function __construct() {

        $contentType = $_SERVER['CONTENT_TYPE'] ?? "";

        if(stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input'); 
            $this->data = json_decode($raw, true);
        } else {
            $this->data = $_POST;
        }

        foreach ($this->data as $key => $value) {
            if (h($value) === '') {
                $this->errors[$key] = "{$key} is required";
            }
        }
    }

    public function input($key, $default = []): array {
        $this->errors($key);
        return $this->data[$key] ?? $default; 
    }

    public function all(): array {
        return $this->data;
    }

    public function errors(string $key = '') {

        if ($key !== '' && isset($this->errors[$key])) {
            return $this->errors[$key];
        }
    
        return $this->errors ?? [];
    }

    public function hasError(): bool {
        return !empty($this->errors);
    }

    public static function make() {
        return new self();
    }
}