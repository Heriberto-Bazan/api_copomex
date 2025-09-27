<?php
// app/Console/Commands/LimpiarCacheCopomexCommand.php

namespace App\Console\Commands;

use App\Services\Interfaces\CopomexServiceInterface;
use Illuminate\Console\Command;

class LimpiarCacheCopomexCommand extends Command
{
    protected $signature = 'copomex:clear-cache 
                          {tipo? : Tipo de cache a limpiar (estados, municipios, all)}
                          {--estado= : Estado específico para limpiar cache de municipios}';
    
    protected $description = 'Limpiar cache de COPOMEX';

    public function handle(CopomexServiceInterface $copomexService): int
    {
        $tipo = $this->argument('tipo') ?? 'all';

        $this->info("🧹 Limpiando cache de COPOMEX...");
        $this->newLine();

        match ($tipo) {
            'estados' => $this->limpiarCacheEstados($copomexService),
            'municipios' => $this->limpiarCacheMunicipios($copomexService),
            'all' => $this->limpiarTodoCache($copomexService),
            default => $this->error("Tipo de cache inválido: {$tipo}. Use: estados, municipios, all")
        };

        return self::SUCCESS;
    }

    private function limpiarCacheEstados(CopomexServiceInterface $copomexService): void
    {
        $this->info('Limpiando cache de estados...');
        
        try {
            $resultado = $copomexService->clearEstadosCache();
            
            if ($resultado) {
                $this->line('<fg=green> Cache de estados limpiado exitosamente</fg=green>');
            } else {
                $this->line('<fg=yellow>  No se pudo limpiar el cache de estados</fg=yellow>');
            }
        } catch (\Exception $e) {
            $this->line('<fg=red> Error al limpiar cache de estados: ' . $e->getMessage() . '</fg=red>');
        }
    }

    private function limpiarCacheMunicipios(CopomexServiceInterface $copomexService): void
    {
        $estado = $this->option('estado');
        
        if (!$estado) {
            $this->error('Especifica el estado con --estado=NombreEstado');
            $this->line('Ejemplo: php artisan copomex:clear-cache municipios --estado="Aguascalientes"');
            return;
        }

        $this->info("Limpiando cache de municipios para {$estado}...");
        
        try {
            $resultado = $copomexService->clearMunicipiosCache($estado);
            
            if ($resultado) {
                $this->line("<fg=green> Cache de municipios de {$estado} limpiado exitosamente</fg=green>");
            } else {
                $this->line("<fg=yellow>  No se pudo limpiar el cache de municipios de {$estado}</fg=yellow>");
            }
        } catch (\Exception $e) {
            $this->line('<fg=red> Error al limpiar cache de municipios: ' . $e->getMessage() . '</fg=red>');
        }
    }

    private function limpiarTodoCache(CopomexServiceInterface $copomexService): void
    {
        $this->info('Limpiando todo el cache de COPOMEX...');
        
        try {
            // Limpiar cache de estados
            $estadosResult = $copomexService->clearEstadosCache();
            
            // Limpiar todo el cache
            $allResult = $copomexService->clearAllCache();
            
            if ($estadosResult && $allResult) {
                $this->line('<fg=green> Todo el cache de COPOMEX ha sido limpiado exitosamente</fg=green>');
                
                $this->table(
                    ['Componente', 'Estado'],
                    [
                        ['Cache de Estados', 'Limpiado'],
                        ['Cache de Municipios', 'Limpiado'],
                        ['Cache General', 'Limpiado'],
                    ]
                );
            } else {
                $this->line('<fg=yellow>⚠️  Algunos elementos del cache no pudieron ser limpiados</fg=yellow>');
            }
        } catch (\Exception $e) {
            $this->line('<fg=red> Error al limpiar cache: ' . $e->getMessage() . '</fg=red>');
        }
    }
}