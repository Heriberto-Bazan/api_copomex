<?php

namespace App\Exceptions;

use Exception;

class EstadoValidationException extends Exception
{
    protected $code = 422;
    private array $errors;

    public function __construct(string $message = 'Error de validación en datos del estado', array $errors = [])
    {
        parent::__construct($message, 422);
        $this->errors = $errors;
    }

    public static function emptyName(): self
    {
        return new self('El nombre del estado no puede estar vacío');
    }

    public static function duplicateName(string $nombre): self
    {
        return new self("Ya existe un estado con el nombre '{$nombre}'");
    }

    public function getErrors(): array
    {
        return $this->errors ?? [];
    }
}
