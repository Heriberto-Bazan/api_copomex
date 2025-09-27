<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CargarEstadosRequest;
use App\Http\Requests\MunicipiosRequest;
use App\Services\Interfaces\CopomexServiceInterface;
use App\Repositories\Interfaces\EstadoRepositoryInterface;
use App\DTOs\EstadoDTO;
use App\DTOs\ResponseDTO;
use App\Exceptions\CopomexApiException;
use App\Exceptions\EstadoNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class EstadosController extends Controller
{
    public function __construct(
        private CopomexServiceInterface $copomexService,
        private EstadoRepositoryInterface $estadoRepository
    ) {}

    /**
     * Mostrar la vista principal de estados
     */
    public function index()
    {
        return view('estados.index', [
            'titulo' => 'Estados de México - COPOMEX',
            'totalEstados' => $this->estadoRepository->count()
        ]);
    }

    /**
     * Cargar estados desde COPOMEX a la base de datos
     */
    public function cargarEstados(CargarEstadosRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validatedWithDefaults();
            
            Log::info('Iniciando carga de estados desde COPOMEX', [
                'params' => $validatedData,
                'user_agent' => $request->userAgent()
            ]);

            // Limpiar cache si se solicita
            if ($validatedData['clear_cache']) {
                $this->copomexService->clearEstadosCache();
                Log::info('Cache de estados limpiado');
            }

            // Verificar si ya existen estados
            $existingCount = $this->estadoRepository->count();
            if ($existingCount > 0 && !$validatedData['force']) {
                return response()->json(
                    ResponseDTO::error(
                        "Ya existen {$existingCount} estados en la base de datos. Use 'force: true' para recargar.",
                        [],
                        409
                    )->toArray(),
                    409
                );
            }

            // Obtener estados desde COPOMEX
            $copomexResponse = $this->copomexService->getEstados();
            $estadosData = $copomexResponse->getEstados();

            if (empty($estadosData)) {
                Log::warning('COPOMEX devolvió lista vacía de estados');
                return response()->json(
                    ResponseDTO::error('No se obtuvieron estados desde COPOMEX')->toArray(),
                    500
                );
            }

            // Convertir a DTOs
            $estadosDTOs = array_map(function ($estadoNombre) {
                return EstadoDTO::fromCopomexResponse($estadoNombre);
            }, $estadosData);

            // Insertar en base de datos usando repository
            $resultado = $this->estadoRepository->bulkCreateOrUpdate($estadosDTOs);

            Log::info('Estados cargados exitosamente', $resultado);

            $responseData = [
                'estados_insertados' => $resultado['insertados'],
                'estados_actualizados' => $resultado['actualizados'],
                'total_procesados' => $resultado['total_procesados'],
                'errores' => $resultado['errores'],
                'total_en_bd' => $this->estadoRepository->count(),
                'estados_copomex' => count($estadosData)
            ];

            $message = "Estados cargados exitosamente. " .
                      "Insertados: {$resultado['insertados']}, " .
                      "Actualizados: {$resultado['actualizados']}";

            return response()->json(
                ResponseDTO::success($message, $responseData)->toArray()
            );

        } catch (CopomexApiException $e) {
            Log::error('Error de COPOMEX API', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return response()->json(
                ResponseDTO::error(
                    'Error al conectar con COPOMEX: ' . $e->getMessage(),
                    [],
                    $e->getCode()
                )->toArray(),
                $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500
            );

        } catch (\Exception $e) {
            Log::error('Error inesperado al cargar estados', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                ResponseDTO::error('Error interno del servidor')->toArray(),
                500
            );
        }
    }

    /**
     * DataTables endpoint para el listado de estados
     */
    public function datatable(Request $request)
    {
        try {
            $estados = $this->estadoRepository->getAll()
                ->map(function ($estado) {
                    return [
                        'id' => $estado->id,
                        'nombre' => $estado->nombre,
                        'created_at' => $estado->created_at->format('d/m/Y H:i:s'),
                        'updated_at' => $estado->updated_at->format('d/m/Y H:i:s'),
                    ];
                });

            return DataTables::of($estados)
                ->addColumn('acciones', function ($estado) {
                    return '<button type="button" class="btn btn-primary btn-sm ver-municipios" 
                                   data-estado="' . htmlspecialchars($estado['nombre']) . '"
                                   data-bs-toggle="tooltip" 
                                   data-bs-placement="top" 
                                   title="Ver municipios de ' . htmlspecialchars($estado['nombre']) . '">
                                <i class="fas fa-eye"></i> 
                                <span class="d-none d-md-inline">Ver Municipios</span>
                            </button>';
                })
                ->editColumn('nombre', function ($estado) {
                    return '<strong>' . htmlspecialchars($estado['nombre']) . '</strong>';
                })
                ->addColumn('municipios_link', function ($estado) {
                    $url = route('estados.municipios', ['estado' => $estado['nombre']]);
                    return '<a href="' . $url . '" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-link"></i> API
                            </a>';
                })
                ->rawColumns(['acciones', 'nombre', 'municipios_link'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error en DataTables de estados', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al cargar los datos'
            ], 500);
        }
    }

    /**
     * Obtener municipios de un estado específico
     */
    public function municipios(MunicipiosRequest $request, string $nombreEstado): JsonResponse
    {
        try {
            $estadoNormalizado = $request->getEstadoNormalizado();
            
            Log::info('Obteniendo municipios', [
                'estado' => $estadoNormalizado,
                'ip' => $request->ip()
            ]);

            // Verificar que el estado existe en la BD
            $estado = $this->estadoRepository->findByName($estadoNormalizado);
            if (!$estado) {
                throw new EstadoNotFoundException($estadoNormalizado);
            }

            // Obtener municipios desde COPOMEX
            $municipiosCollection = $this->copomexService->getMunicipiosPorEstado($estadoNormalizado);
            Log::info('Municipios obtenidos exitosamente', [
                'estado' => $estadoNormalizado,
                'total_municipios' => $municipiosCollection->total
            ]);

            return response()->json(
                ResponseDTO::success(
                    "Municipios de {$estadoNormalizado} obtenidos exitosamente",
                    [
                        'estado' => $estadoNormalizado,
                        'total' => $municipiosCollection->total,
                        'municipios' => $municipiosCollection->getMunicipiosNames(),
                        'municipios_detallados' => $municipiosCollection->toArray()
                    ]
                )->toArray()
            );

        } catch (EstadoNotFoundException $e) {
            Log::warning('Estado no encontrado', [
                'estado' => $nombreEstado,
                'error' => $e->getMessage()
            ]);

            return response()->json(
                ResponseDTO::error($e->getMessage(), [], 404)->toArray(),
                404
            );

        } catch (CopomexApiException $e) {
            Log::error('Error de COPOMEX al obtener municipios', [
                'estado' => $estadoNormalizado ?? $nombreEstado,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return response()->json(
                ResponseDTO::error(
                    'Error al obtener municipios desde COPOMEX: ' . $e->getMessage(),
                    [],
                    $e->getCode()
                )->toArray(),
                $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500
            );

        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener municipios', [
                'estado' => $nombreEstado,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(
                ResponseDTO::error('Error interno del servidor')->toArray(),
                500
            );
        }
    }

    /**
     * API endpoint - Obtener todos los estados (JSON)
     */
    public function apiIndex(): JsonResponse
    {
        try {
            $estados = $this->estadoRepository->getAll()
                ->map(fn($estado) => EstadoDTO::fromModel($estado)->toArray());

            return response()->json(
                ResponseDTO::success(
                    'Estados obtenidos exitosamente',
                    [
                        'total' => count($estados),
                        'estados' => $estados
                    ]
                )->toArray()
            );

        } catch (\Exception $e) {
            Log::error('Error en API de estados', [
                'error' => $e->getMessage()
            ]);

            return response()->json(
                ResponseDTO::error('Error al obtener estados')->toArray(),
                500
            );
        }
    }

    /**
     * Limpiar cache de COPOMEX
     */
    public function limpiarCache(Request $request): JsonResponse
    {
        try {
            $tipo = $request->input('tipo', 'all');
            
            $resultado = match ($tipo) {
                'estados' => $this->copomexService->clearEstadosCache(),
                'all' => $this->copomexService->clearAllCache(),
                default => false
            };

            if ($resultado) {
                Log::info('Cache limpiado', ['tipo' => $tipo]);
                
                return response()->json(
                    ResponseDTO::success("Cache de {$tipo} limpiado exitosamente")->toArray()
                );
            }

            return response()->json(
                ResponseDTO::error('No se pudo limpiar el cache')->toArray(),
                500
            );

        } catch (\Exception $e) {
            Log::error('Error al limpiar cache', [
                'error' => $e->getMessage()
            ]);

            return response()->json(
                ResponseDTO::error('Error al limpiar cache')->toArray(),
                500
            );
        }
    }

    /**
     * Estado de salud del servicio
     */
    public function health(): JsonResponse
    {
        try {
            $copomexStatus = $this->copomexService->checkConnection();
            $dbStatus = $this->estadoRepository->count() >= 0;
            $totalEstados = $this->estadoRepository->count();

            $isHealthy = $copomexStatus && $dbStatus;

            return response()->json([
                'status' => $isHealthy ? 'healthy' : 'degraded',
                'timestamp' => now()->toISOString(),
                'services' => [
                    'copomex_api' => $copomexStatus ? 'up' : 'down',
                    'database' => $dbStatus ? 'up' : 'down'
                ],
                'data' => [
                    'total_estados' => $totalEstados,
                    'expected_estados' => 32
                ],
                'version' => '1.0.0'
            ], $isHealthy ? 200 : 503);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'unhealthy',
                'error' => 'Service check failed',
                'timestamp' => now()->toISOString()
            ], 503);
        }
    }
}