<?php

namespace Core;
use Core\Validator;

class Requests {

    private array $data;
    
    private ?Validator $validator = null;

    public function __construct()
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
        
        $this->data = array_map('h', $input);
    }

    public function all(): array
    {
        return $this->data;
    }

    public function validate(array $rules): self
    {
        $this->validator = new Validator($this->data, $rules);
        return $this;
    }

    public function fails(): bool
    {
        return !$this->validator?->passes();
    }

    public function errors(): array
    {
        return $this->validator?->errors() ?? [];
    }

    public static function make(): self
    {
        return new self();
    }
}