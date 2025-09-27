<?php
// app/Services/CopomexService.php

namespace App\Services;

use App\DTOs\CopomexResponseDTO;
use App\DTOs\MunicipiosCollectionDTO;
use App\Exceptions\CopomexApiException;
use App\Services\Interfaces\CopomexServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CopomexService implements CopomexServiceInterface
{
    private string $baseUrl;
    private string $token;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.copomex.base_url');
        $this->token = config('services.copomex.token');
        $this->timeout = config('services.copomex.timeout', 30);
    }

    /**
     * Obtener todos los estados de México desde COPOMEX
     */
    public function getEstados(): CopomexResponseDTO
    {
        $cacheKey = 'copomex_estados';

        try {
            // Intentar obtener desde cache primero (cache por 1 hora)
            $cachedResponse = Cache::remember($cacheKey, 3600, function () {
                return $this->fetchEstadosFromApi();
            });

            if ($cachedResponse) {
                Log::info('Estados obtenidos desde COPOMEX', [
                    'source' => Cache::has($cacheKey) ? 'cache' : 'api',
                    'estados_count' => count($cachedResponse->getEstados())
                ]);

                return $cachedResponse;
            }

            throw CopomexApiException::invalidResponse('Respuesta vacía de la API');
        } catch (CopomexApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener estados', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw CopomexApiException::connectionError();
        }
    }

    /**
     * Obtener municipios de un estado específico
     */
    public function getMunicipiosPorEstado(string $nombreEstado): MunicipiosCollectionDTO
    {
        $estadoNormalizado = trim($nombreEstado);
        $cacheKey = 'copomex_municipios_' . md5($estadoNormalizado);

        try {
            // Cache por 2 horas para municipios
            $municipios = Cache::remember($cacheKey, 7200, function () use ($estadoNormalizado) {
                return $this->fetchMunicipiosFromApi($estadoNormalizado);
            });

            Log::info('Municipios obtenidos desde COPOMEX', [
                'estado' => $estadoNormalizado,
                'municipios_count' => $municipios->total,
                'source' => Cache::has($cacheKey) ? 'cache' : 'api'
            ]);

            return $municipios;
        } catch (CopomexApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener municipios', [
                'estado' => $estadoNormalizado,
                'error' => $e->getMessage()
            ]);

            throw CopomexApiException::connectionError();
        }
    }

    /**
     * Verificar conectividad con la API de COPOMEX
     */
    public function checkConnection(): bool
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 1000)
                ->get("{$this->baseUrl}/info", [
                    'token' => $this->token
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Fallo en verificación de conectividad COPOMEX', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Obtener configuración actual del servicio
     */
    public function getConfig(): array
    {
        return [
            'base_url' => $this->baseUrl,
            'token' => $this->maskToken($this->token),
            'timeout' => $this->timeout,
            'connection_status' => $this->checkConnection()
        ];
    }

    /**
     * Hacer petición a la API para obtener estados
     */
    private function fetchEstadosFromApi(): CopomexResponseDTO
    {
        $response = Http::timeout($this->timeout)
            ->retry(3, 2000)
            ->get("{$this->baseUrl}/get_estados", [
                'token' => $this->token
            ]);

        if (!$response->successful()) {
            $this->logApiError('get_estados', $response);

            throw match ($response->status()) {
                401, 403 => CopomexApiException::apiError('Token inválido o sin permisos'),
                404 => CopomexApiException::apiError('Endpoint no encontrado'),
                429 => CopomexApiException::apiError('Límite de peticiones excedido'),
                500, 502, 503, 504 => CopomexApiException::apiError('Error del servidor COPOMEX'),
                default => CopomexApiException::connectionError()
            };
        }

        $data = $response->json();

        // Debug - ver qué devuelve realmente la API
        Log::info('COPOMEX API Response', [
            'data' => $data,
            'url' => "{$this->baseUrl}/get_estados",
            'token' => $this->maskToken($this->token)
        ]);

        $copomexResponse = CopomexResponseDTO::fromApiResponse($data);

        if (!$copomexResponse->isSuccess()) {
            Log::error('COPOMEX API Error', [
                'error' => $copomexResponse->error,
                'code_error' => $copomexResponse->codeError,
                'error_message' => $copomexResponse->errorMessage
            ]);
            throw CopomexApiException::apiError($copomexResponse->getErrorMessage());
        }

        $estados = $copomexResponse->getEstados();
        if (empty($estados)) {
            throw CopomexApiException::invalidResponse('No se encontraron estados en la respuesta');
        }

        Log::info('Estados obtenidos exitosamente', [
            'total' => count($estados),
            'ejemplos' => array_slice($estados, 0, 5)
        ]);

        return $copomexResponse;
    }
    /**
     * Hacer petición a la API para obtener municipios
     */
    private function fetchMunicipiosFromApi(string $nombreEstado): MunicipiosCollectionDTO
    {
        $estadoCodificado = urlencode($nombreEstado);

        $response = Http::timeout($this->timeout)
            ->retry(3, 2000)
            ->get("{$this->baseUrl}/get_municipio_por_estado/{$estadoCodificado}", [
                'token' => $this->token
            ]);

        if (!$response->successful()) {
            $this->logApiError('get_municipios', $response, ['estado' => $nombreEstado]);

            throw match ($response->status()) {
                401, 403 => CopomexApiException::apiError('Token inválido o sin permisos'),
                404 => CopomexApiException::apiError("Estado '{$nombreEstado}' no encontrado"),
                429 => CopomexApiException::apiError('Límite de peticiones excedido'),
                500, 502, 503, 504 => CopomexApiException::apiError('Error del servidor COPOMEX'),
                default => CopomexApiException::connectionError()
            };
        }

        $data = $response->json();

        // Debug - ver qué devuelve realmente la API para municipios
        Log::info('COPOMEX API Response - Municipios', [
            'estado' => $nombreEstado,
            'data' => $data,
            'url' => "{$this->baseUrl}/get_municipio_por_estado/{$estadoCodificado}"
        ]);

        $copomexResponse = CopomexResponseDTO::fromApiResponse($data);

        if (!$copomexResponse->isSuccess()) {
            Log::error('COPOMEX API Error - Municipios', [
                'estado' => $nombreEstado,
                'error' => $copomexResponse->error,
                'code_error' => $copomexResponse->codeError,
                'error_message' => $copomexResponse->errorMessage
            ]);
            throw CopomexApiException::apiError($copomexResponse->getErrorMessage());
        }

        $municipiosData = $copomexResponse->getMunicipios();

        if (empty($municipiosData)) {
            throw CopomexApiException::invalidResponse("No se encontraron municipios para el estado '{$nombreEstado}'");
        }

        Log::info('Municipios obtenidos exitosamente', [
            'estado' => $nombreEstado,
            'total' => count($municipiosData),
            'ejemplos' => array_slice($municipiosData, 0, 5)
        ]);

        return MunicipiosCollectionDTO::fromCopomexResponse($municipiosData, $nombreEstado);
    }

    /**
     * Log de errores de la API
     */
    private function logApiError($endpoint, $response, array $context = []): void
    {
        Log::error('Error en API COPOMEX', array_merge([
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'response_body' => $response->body(),
            'headers' => $response->headers()
        ], $context));
    }

    /**
     * Enmascarar token para logs
     */
    private function maskToken(string $token): string
    {
        if (strlen($token) <= 4) {
            return '***';
        }

        return substr($token, 0, 2) . str_repeat('*', strlen($token) - 4) . substr($token, -2);
    }

    /**
     * Limpiar cache de estados
     */
    public function clearEstadosCache(): bool
    {
        return Cache::forget('copomex_estados');
    }

    /**
     * Limpiar cache de municipios de un estado
     */
    public function clearMunicipiosCache(string $nombreEstado): bool
    {
        $cacheKey = 'copomex_municipios_' . md5(trim($nombreEstado));
        return Cache::forget($cacheKey);
    }

    /**
     * Limpiar todo el cache de COPOMEX
     */
    public function clearAllCache(): bool
    {
        $keys = Cache::getRedis()->keys('*copomex*');

        if (empty($keys)) {
            return true;
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }

        return true;
    }
}
