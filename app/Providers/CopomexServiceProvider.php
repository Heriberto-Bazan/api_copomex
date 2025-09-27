<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CopomexService;
use App\Services\Interfaces\CopomexServiceInterface;

class CopomexServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar configuraciones de COPOMEX
        $this->mergeConfigFrom(
            __DIR__.'/../../config/copomex.php', 'copomex'
        );

        // Registrar el servicio
        $this->app->singleton(CopomexServiceInterface::class, CopomexService::class);

        // Bind alternativo por nombre de clase
        $this->app->bind('copomex', CopomexServiceInterface::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publicar configuración
        $this->publishes([
            __DIR__.'/../../config/copomex.php' => config_path('copomex.php'),
        ], 'copomex-config');

        // Registrar comandos Artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\CargarEstadosCommand::class,
                \App\Console\Commands\LimpiarCacheCopomexCommand::class,
            ]);
        }

        // Configurar cache tags si se usa Redis
        if (config('cache.default') === 'redis') {
            $this->app->make('cache')->tags(['copomex']);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            CopomexServiceInterface::class,
            'copomex',
        ];
    }
}