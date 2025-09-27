<?php

namespace App\DTOs;

class MunicipioDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $estado
    ) {}

    public static function fromCopomexResponse(string $municipioNombre, string $estadoNombre): self
    {
        return new self(
            nombre: trim($municipioNombre),
            estado: trim($estadoNombre)
        );
    }

    public function toArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'estado' => $this->estado,
        ];
    }
}
