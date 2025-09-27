<?php

namespace App\Exceptions;

use Exception;

class EstadoNotFoundException extends Exception
{
    protected $code = 404;

    public function __construct(string $estadoNombre)
    {
        $message = "El estado '{$estadoNombre}' no fue encontrado en la base de datos";
        parent::__construct($message, 404);
    }
}
