<?php

namespace Core;

class Validator
{
    protected array $data;
    protected array $rules;
    protected array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    protected function validate(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            $value = h($this->data[$field]) ?? null;

            foreach ($rules as $rule) {
                if ($rule === 'required' && empty($value)) {
                    if(!isset($this->errors[$field])) {
                        $this->errors[$field][] = "{$field} is required.";
                    }
                }

                if ($rule === 'string' && !is_string($value)) {
                    if(!iseet($this->errors[$field])) {
                        $this->errors[$field][] = "{$field} must be a string.";
                    }
                }

                if ($rule === 'number' && !is_numeric($value)) {
                    if(!isset($this->errors[$field])) {
                        $this->errors[$field][] = "{$field} must be numeric.";
                    }
                }

                if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    if(!isset($this->errors[$field])) {
                        $this->errors[$field][] = "{$field} must be a valid email address.";
                    }
                }

                if ($rule === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    if(!isset($this->errors[$field])) {
                        $this->errors[$field][] = "{$field} must be a valid URL.";
                    }
                }

                if($rule === 'float' && !is_float($value) && !is_numeric($value)) {
                    if(!isset($this->errors[$field])) {
                        $this->errors[$field][] = "{$field} must be a float.";
                    }
                }

                if($rule === 'boolean' && !is_bool($value) && !in_array($value, ['true', 'false', 1, 0], true)) {
                    if(!isset($this->errors[$field])) {
                        $this->errors[$field][] = "{$field} must be a boolean.";
                    }
                    
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) explode(':', $rule)[1];
                    if (is_string($value) && mb_strlen($value) > $max) {
                        if(!isset($this->errors[$field])) {
                            $this->errors[$field][] = "{$field} must not exceed {$max} characters.";
                        }
                    }
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) explode(':', $rule)[1];
                    if (is_string($value) && count(explode(" ", $value)) < $min) {
                        if(!isset($this->errors[$field])) {
                            $this->errors[$field][] = "{$field} must be at least {$min} words.";
                        }
                    }
                }
            }
        }
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}