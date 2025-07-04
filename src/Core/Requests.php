<?php 

namespace src\Core;

use src\Core\Validator;

class Requests {

    private array $data;
    
    private ?Validator $validator = null;

    public function __construct()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? "";
        $raw = file_get_contents('php://input');

        $this->data = stripos($contentType, 'application/json') !== false
            ? json_decode($raw, true)
            : $_POST;
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