<?php
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field] = "O campo '$label' é obrigatório.";
        }
        return $this;
    }

    public function email(string $field): self
    {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'E-mail inválido.';
        }
        return $this;
    }

    public function min(string $field, int $min, string $label = ''): self
    {
        $label = $label ?: $field;
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field] = "'$label' deve ter no mínimo $min caracteres.";
        }
        return $this;
    }

    public function max(string $field, int $max, string $label = ''): self
    {
        $label = $label ?: $field;
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) > $max) {
            $this->errors[$field] = "'$label' deve ter no máximo $max caracteres.";
        }
        return $this;
    }

    public function in(string $field, array $allowed): self
    {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $this->errors[$field] = "Valor inválido para '$field'. Permitidos: " . implode(', ', $allowed);
        }
        return $this;
    }

    public function integer(string $field): self
    {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "'$field' deve ser um número inteiro.";
        }
        return $this;
    }

    public function date(string $field, string $format = 'Y-m-d H:i:s'): self
    {
        if (isset($this->data[$field])) {
            $d = DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d) {
                $this->errors[$field] = "Data inválida para '$field'. Formato esperado: $format";
            }
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function firstError(): array
    {
        $field   = array_key_first($this->errors);
        $message = $this->errors[$field];
        return ['campo' => $field, 'mensagem' => $message];
    }

    public function failOrContinue(): void
    {
        if (!$this->passes()) {
            $err = $this->firstError();
            Response::unprocessable($err['campo'], $err['mensagem']);
        }
    }

    public function get(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }
}
