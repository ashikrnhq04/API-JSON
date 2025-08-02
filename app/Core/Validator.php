<?php
namespace Core;
/**
 * Class Validator
 *
 * Validates input data against a set of rules. Supports required, type, and format checks.
 */
class Validator
{
    /**
     * Input data to validate.
     * @var array
     */
    protected array $data;

    /**
     * Validation rules for each field.
     * @var array
     */
    protected array $rules;

    /**
     * Validation errors.
     * @var array
     */
    protected array $errors = [];

    /**
     * Create a new Validator instance and run validation.
     * @param array $data
     * @param array $rules
     */
    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->validate();
    }

    /**
     * Run validation for all fields.
     */
    protected function validate(): void
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;
            $this->validateField($field, $value, $rules);
        }
    }

    /**
     * Validate a single field against its rules.
     * @param string $field
     * @param mixed $value
     * @param array $rules
     */
    protected function validateField(string $field, $value, array $rules): void
    {
        $isRequired = in_array('required', $rules);
        // Check required first
        if (!$this->validateRequired($field, $value, $isRequired)) {
            return; // Skip further validation if required fails
        }
        // If not required and value is empty, skip type checks
        if (!$isRequired && $this->isEmpty($value)) {
            return;
        }
        // Validate each rule
        foreach ($rules as $rule) {
            $this->applyRule($field, $value, $rule);
        }
    }

    /**
     * Check if a field is required and present.
     * @param string $field
     * @param mixed $value
     * @param bool $isRequired
     * @return bool
     */
    protected function validateRequired(string $field, $value, bool $isRequired): bool
    {
        if ($isRequired && $this->isEmpty(h($value))) {
            $this->addError($field, "{$field} is required.");
            return false;
        }
        return true;
    }

    /**
     * Check if a value is empty.
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value): bool
    {
        return is_null($value) || $value === '' || (is_array($value) && empty($value));
    }

    /**
     * Apply a validation rule to a field.
     * @param string $field
     * @param mixed $value
     * @param string $rule
     */
    protected function applyRule(string $field, $value, string $rule): void
    {
        switch ($rule) {
            case 'string':
                $this->validateString($field, $value);
                break;
            case 'number':
                $this->validateNumber($field, $value);
                break;
            case 'email':
                $this->validateEmail($field, $value);
                break;
            case 'url':
                $this->validateUrl($field, $value);
                break;
            case 'float':
                $this->validateFloat($field, $value);
                break;
            case 'boolean':
                $this->validateBoolean($field, $value);
                break;
            case 'array':
                $this->validateArray($field, $value);
                break;
            default:
                if (str_starts_with($rule, 'max:')) {
                    $this->validateMax($field, $value, $rule);
                } elseif (str_starts_with($rule, 'min:')) {
                    $this->validateMin($field, $value, $rule);
                }
                break;
        }
    }

    protected function validateString(string $field, $value): void
    {
        if (!is_string($value)) {
            $this->addError($field, "{$field} must be a string.");
        }
    }

    protected function validateNumber(string $field, $value): void
    {
        if (!is_numeric($value)) {
            $this->addError($field, "{$field} must be numeric.");
        }
    }

    protected function validateEmail(string $field, $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "{$field} must be a valid email address.");
        }
    }

    protected function validateUrl(string $field, $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "{$field} must be a valid URL.");
        }
    }

    protected function validateFloat(string $field, $value): void
    {
        if (!is_float($value) && !is_numeric($value)) {
            $this->addError($field, "{$field} must be a float.");
        }
    }

    protected function validateBoolean(string $field, $value): void
    {
        if (!is_bool($value) && !in_array($value, ['true', 'false', 1, 0], true)) {
            $this->addError($field, "{$field} must be a boolean.");
        }
    }

    protected function validateMax(string $field, $value, string $rule): void
    {
        $max = (int) explode(':', $rule)[1];
        
        if (is_string($value)) {
            $wordCount = str_word_count($value);
            if ($wordCount > $max) {
                $this->addError($field, "{$field} must not exceed {$max} words.");
            }
        } elseif (is_numeric($value) && $value > $max) {
            $this->addError($field, "{$field} must not be greater than {$max}.");
        }
    }

    public function validateArray(string $field, $value): void
    {
        if (!is_array($value)) {
            $this->addError($field, "{$field} must be an array.");
        } else {
            foreach ($value as $item) {
                if (!is_string($item)) {
                    $this->addError($field, "Each item in {$field} must be a string.");
                }
            }
        }
    }

    protected function validateMin(string $field, $value, string $rule): void
    {
        $min = (int) explode(':', $rule)[1];
        
        if (is_string($value)) {
            $wordCount = str_word_count($value);
            if ($wordCount < $min) {
                $this->addError($field, "{$field} must be at least {$min} words.");
            }
        } elseif (is_numeric($value) && $value < $min) {
            $this->addError($field, "{$field} must not be less than {$min}.");
        }
    }

    protected function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
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