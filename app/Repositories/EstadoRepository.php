<?php

namespace App\Repositories;

use App\DTOs\EstadoDTO;
use App\Models\Estado;
use App\Repositories\Interfaces\EstadoRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EstadoRepository implements EstadoRepositoryInterface
{
    public function __construct(
        private Estado $model
    ) {}

    /**
     * Obtener todos los estados
     */
    public function getAll(): Collection
    {
        return $this->model->orderBy('nombre')->get();
    }

    /**
     * Buscar estado por nombre
     */
    public function findByName(string $nombre): ?Estado
    {
        return $this->model->where('nombre', trim($nombre))->first();
    }

    /**
     * Crear o actualizar estado
     */
    public function createOrUpdate(EstadoDTO $estadoDTO): Estado
    {
        return $this->model->updateOrCreate(
            ['nombre' => $estadoDTO->nombre],
            $estadoDTO->toModelArray()
        );
    }

    /**
     * Crear múltiples estados de forma eficiente
     */
    public function bulkCreateOrUpdate(array $estadosDTOs): array
    {
        $insertados = 0;
        $actualizados = 0;
        $errores = [];

        DB::beginTransaction();

        try {
            foreach ($estadosDTOs as $estadoDTO) {
                if (!$estadoDTO instanceof EstadoDTO) {
                    $errores[] = 'DTO inválido encontrado';
                    continue;
                }

                $existingEstado = $this->findByName($estadoDTO->nombre);

                if ($existingEstado) {
                    // Actualizar si hay cambios
                    $existingEstado->update($estadoDTO->toModelArray());
                    $actualizados++;
                } else {
                    // Crear nuevo
                    $this->model->create($estadoDTO->toModelArray());
                    $insertados++;
                }
            }

            DB::commit();

            Log::info('Bulk operation completed', [
                'insertados' => $insertados,
                'actualizados' => $actualizados,
                'errores' => count($errores)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk operation: ' . $e->getMessage());
            throw $e;
        }

        return [
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'errores' => $errores,
            'total_procesados' => $insertados + $actualizados
        ];
    }

    /**
     * Verificar si existe un estado por nombre
     */
    public function existsByName(string $nombre): bool
    {
        return $this->model->where('nombre', trim($nombre))->exists();
    }

    /**
     * Obtener conteo total de estados
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Obtener estados paginados
     */
    public function getPaginated(int $perPage = 25): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->orderBy('nombre')->paginate($perPage);
    }

    /**
     * Buscar estados por nombre (búsqueda parcial)
     */
    public function searchByName(string $searchTerm): Collection
    {
        return $this->model
            ->where('nombre', 'LIKE', '%' . trim($searchTerm) . '%')
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Eliminar estado por ID
     */
    public function deleteById(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    /**
     * Obtener estado por ID
     */
    public function findById(int $id): ?Estado
    {
        return $this->model->find($id);
    }
}