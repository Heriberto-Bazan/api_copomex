<?php

namespace App\Services\Interfaces;

use App\DTOs\CopomexResponseDTO;
use App\DTOs\MunicipiosCollectionDTO;

interface CopomexServiceInterface
{
    /**
     * Obtener todos los estados de México desde COPOMEX
     */
    public function getEstados(): CopomexResponseDTO;

    /**
     * Obtener municipios de un estado específico
     */
    public function getMunicipiosPorEstado(string $nombreEstado): MunicipiosCollectionDTO;

    /**
     * Verificar conectividad con la API de COPOMEX
     */
    public function checkConnection(): bool;

    /**
     * Obtener configuración actual del servicio
     */
    public function getConfig(): array;

    /**
     * Limpiar cache de estados
     */
    public function clearEstadosCache(): bool;

    /**
     * Limpiar cache de municipios de un estado
     */
    public function clearMunicipiosCache(string $nombreEstado): bool;

    /**
     * Limpiar todo el cache de COPOMEX
     */
    public function clearAllCache(): bool;
}
