<?php

namespace App\Console\Commands;

use App\Services\Interfaces\CopomexServiceInterface;
use App\Repositories\Interfaces\EstadoRepositoryInterface;
use App\DTOs\EstadoDTO;
use App\Exceptions\CopomexApiException;
use Illuminate\Console\Command;

class CargarEstadosCommand extends Command
{
    protected $signature = 'estados:cargar {--force : Forzar recarga sin preguntar}';
    protected $description = 'Cargar estados desde COPOMEX API a la base de datos';

    public function handle(
        CopomexServiceInterface $copomexService,
        EstadoRepositoryInterface $estadoRepository
    ): int {
        $this->info('Iniciando carga de estados desde COPOMEX...');
        $this->newLine();

        try {
            // Verificar si ya existen estados
            $existingCount = $estadoRepository->count();
            
            if ($existingCount > 0 && !$this->option('force')) {
                if (!$this->confirm("Ya existen {$existingCount} estados. ¿Continuar con la carga?")) {
                    $this->info('Operación cancelada.');
                    return self::SUCCESS;
                }
            }

            // Obtener estados desde COPOMEX
            $this->info('Obteniendo estados desde COPOMEX...');
            $response = $copomexService->getEstados();
            $estadosData = $response->getEstados();

            if (empty($estadosData)) {
                $this->error('No se obtuvieron estados de COPOMEX');
                return self::FAILURE;
            }

            // Convertir a DTOs
            $estadosDTOs = array_map(function ($estadoNombre) {
                return EstadoDTO::fromCopomexResponse($estadoNombre);
            }, $estadosData);

            // Insertar en base de datos
            $this->info('Guardando estados en base de datos...');
            $progressBar = $this->output->createProgressBar(count($estadosDTOs));

            $resultado = $estadoRepository->bulkCreateOrUpdate($estadosDTOs);

            $progressBar->finish();
            $this->newLine(2);

            // Mostrar resultados
            $this->info('Carga completada exitosamente!');
            $this->table(
                ['Métrica', 'Cantidad'],
                [
                    ['Estados insertados', $resultado['insertados']],
                    ['Estados actualizados', $resultado['actualizados']],
                    ['Total procesados', $resultado['total_procesados']],
                    ['Errores', count($resultado['errores'])],
                ]
            );

            if (!empty($resultado['errores'])) {
                $this->warn('Errores encontrados:');
                foreach ($resultado['errores'] as $error) {
                    $this->line("  • {$error}");
                }
            }

            return self::SUCCESS;

        } catch (CopomexApiException $e) {
            $this->error("Error de COPOMEX: {$e->getMessage()}");
            return self::FAILURE;
        } catch (\Exception $e) {
            $this->error("Error inesperado: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}