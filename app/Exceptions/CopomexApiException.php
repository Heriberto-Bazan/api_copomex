<?php

namespace App\Exceptions;

use Exception;

class CopomexApiException extends Exception
{
    protected $code = 500;

    public function __construct(string $message = 'Error al comunicarse con la API de COPOMEX', int $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function connectionError(): self
    {
        return new self('No se pudo conectar con el servicio de COPOMEX', 503);
    }

    public static function invalidResponse(string $details = ''): self
    {
        $message = 'Respuesta inválida de COPOMEX';
        if ($details) {
            $message .= ': ' . $details;
        }
        return new self($message, 422);
    }

    public static function apiError(string $apiMessage): self
    {
        return new self('Error de COPOMEX: ' . $apiMessage, 400);
    }

    public static function timeout(): self
    {
        return new self('Timeout al conectarse con COPOMEX', 504);
    }
}
