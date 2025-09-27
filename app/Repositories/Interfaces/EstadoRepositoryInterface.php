<?php

namespace App\Repositories\Interfaces;

use App\DTOs\EstadoDTO;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Estado;

interface EstadoRepositoryInterface
{
    /**
     * Obtener todos los estados
     */
    public function getAll(): Collection;

    /**
     * Buscar estado por nombre
     */
    public function findByName(string $nombre): ?Estado;

    /**
     * Crear o actualizar estado
     */
    public function createOrUpdate(EstadoDTO $estadoDTO): Estado;

    /**
     * Crear múltiples estados de forma eficiente
     */
    public function bulkCreateOrUpdate(array $estadosDTOs): array;

    /**
     * Verificar si existe un estado por nombre
     */
    public function existsByName(string $nombre): bool;

    /**
     * Obtener conteo total de estados
     */
    public function count(): int;

    /**
     * Obtener estados paginados
     */
    public function getPaginated(int $perPage = 25): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Buscar estados por nombre (búsqueda parcial)
     */
    public function searchByName(string $searchTerm): Collection;

    /**
     * Eliminar estado por ID
     */
    public function deleteById(int $id): bool;

    /**
     * Obtener estado por ID
     */
    public function findById(int $id): ?Estado;
}