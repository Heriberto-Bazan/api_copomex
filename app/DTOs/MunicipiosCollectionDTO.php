<?php

namespace App\DTOs;

class MunicipiosCollectionDTO
{
    public function __construct(
        public readonly array $municipios,
        public readonly string $estado,
        public readonly int $total
    ) {}

    public static function fromCopomexResponse(array $municipiosData, string $estadoNombre): self
    {
        $municipios = array_map(
            fn($municipio) => MunicipioDTO::fromCopomexResponse($municipio, $estadoNombre),
            $municipiosData
        );

        return new self(
            municipios: $municipios,
            estado: $estadoNombre,
            total: count($municipios)
        );
    }

    public function toArray(): array
    {
        return [
            'estado' => $this->estado,
            'total' => $this->total,
            'municipios' => array_map(
                fn(MunicipioDTO $municipio) => $municipio->toArray(),
                $this->municipios
            )
        ];
    }

    public function getMunicipiosNames(): array
    {
        return array_map(
            fn(MunicipioDTO $municipio) => $municipio->nombre,
            $this->municipios
        );
    }
}