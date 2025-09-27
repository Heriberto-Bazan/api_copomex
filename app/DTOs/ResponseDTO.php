<?php
namespace App\DTOs;

class ResponseDTO
{
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly mixed $data = null,
        public readonly array $errors = [],
        public readonly int $code = 200
    ) {}

    public static function success(string $message, mixed $data = null, int $code = 200): self
    {
        return new self(
            success: true,
            message: $message,
            data: $data,
            code: $code
        );
    }

    public static function error(string $message, array $errors = [], int $code = 500): self
    {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            code: $code
        );
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'errors' => $this->errors,
            'code' => $this->code
        ];
    }
}