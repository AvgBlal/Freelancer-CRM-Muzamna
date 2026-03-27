<?php
/**
 * Input Validation
 */

namespace App\Core;

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, ?string $label = null): self
    {
        if (empty($this->data[$field])) {
            $this->errors[$field] = __('validation.required', ['label' => $label ?? $field]);
        }
        return $this;
    }

    public function email(string $field, ?string $label = null): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = __('validation.invalid', ['label' => $label ?? $field]);
        }
        return $this;
    }

    public function url(string $field, ?string $label = null): self
    {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_URL)) {
            $this->errors[$field] = __('validation.invalid', ['label' => $label ?? $field]);
        }
        return $this;
    }

    public function max(string $field, int $length, ?string $label = null): self
    {
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) > $length) {
            $this->errors[$field] = __('validation.max', ['label' => $label ?? $field, 'length' => $length]);
        }
        return $this;
    }

    public function min(string $field, int $length, ?string $label = null): self
    {
        if (!empty($this->data[$field]) && mb_strlen($this->data[$field]) < $length) {
            $this->errors[$field] = __('validation.min', ['label' => $label ?? $field, 'length' => $length]);
        }
        return $this;
    }

    public function numeric(string $field, ?string $label = null): self
    {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = __('validation.numeric', ['label' => $label ?? $field]);
        }
        return $this;
    }

    public function in(string $field, array $values, ?string $label = null): self
    {
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $values, true)) {
            $this->errors[$field] = __('validation.in', ['label' => $label ?? $field]);
        }
        return $this;
    }

    public function date(string $field, ?string $label = null): self
    {
        if (!empty($this->data[$field])) {
            $date = \DateTime::createFromFormat('Y-m-d', $this->data[$field]);
            if (!$date || $date->format('Y-m-d') !== $this->data[$field]) {
                $this->errors[$field] = __('validation.date', ['label' => $label ?? $field]);
            }
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors[array_key_first($this->errors)] ?? null;
    }
}
