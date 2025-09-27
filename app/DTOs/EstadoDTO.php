<?php

namespace App\DTOs;

use Carbon\Carbon;

class EstadoDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombre,
        public readonly ?string $codigoPostal,
        public readonly ?Carbon $createdAt = null,
        public readonly ?Carbon $updatedAt = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            nombre: trim($data['nombre']),
            codigoPostal: $data['codigo_postal'] ?? null,
            createdAt: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null
        );
    }

    public static function fromModel($modelo): self
    {
        return new self(
            id: $modelo->id,
            nombre: $modelo->nombre,
            codigoPostal: $modelo->codigo_postal,
            createdAt: $modelo->created_at,
            updatedAt: $modelo->updated_at
        );
    }

    public static function fromCopomexResponse(string $estadoNombre): self
    {
        return new self(
            id: null,
            nombre: trim($estadoNombre),
            codigoPostal: null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'codigo_postal' => $this->codigoPostal,
            'created_at' => $this->createdAt?->toISOString(),
            'updated_at' => $this->updatedAt?->toISOString(),
        ];
    }

    public function toModelArray(): array
    {
        return [
            'nombre' => $this->nombre,
            'codigo_postal' => $this->codigoPostal,
        ];
    }
}