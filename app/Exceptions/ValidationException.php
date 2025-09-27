<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class ValidationException extends Exception
{
    protected $code = 422;
    private MessageBag $errors;

    public function __construct(string $message = 'Error de validación', MessageBag $errors = null)
    {
        parent::__construct($message, 422);
        $this->errors = $errors ?? new MessageBag();
    }

    public static function withErrors(array $errors, string $message = 'Error de validación'): self
    {
        $messageBag = new MessageBag($errors);
        return new self($message, $messageBag);
    }

    public function getErrors(): array
    {
        return $this->errors->toArray();
    }

    public function getMessageBag(): MessageBag
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return $this->errors->isNotEmpty();
    }
}