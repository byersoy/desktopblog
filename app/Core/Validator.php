<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];

    public function __construct(private readonly array $data)
    {
    }

    public function required(string $field, string $label): self
    {
        $value = $this->data[$field] ?? '';
        if (is_string($value) ? trim($value) === '' : empty($value)) {
            $this->errors[$field] = "{$label} zorunludur.";
        }
        return $this;
    }

    public function email(string $field, string $label): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} geçerli bir e-posta olmalıdır.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label): self
    {
        $value = (string) ($this->data[$field] ?? '');
        if (mb_strlen($value) < $min) {
            $this->errors[$field] = "{$label} en az {$min} karakter olmalıdır.";
        }
        return $this;
    }

    public function fails(): bool
    {
        return count($this->errors) > 0;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(): ?string
    {
        return $this->errors === [] ? null : array_values($this->errors)[0];
    }
}
