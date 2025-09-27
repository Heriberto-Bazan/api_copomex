<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Services\Interfaces\CopomexServiceInterface;
use App\Services\CopomexService;

use App\Repositories\Interfaces\EstadoRepositoryInterface;
use App\Repositories\EstadoRepository;

use App\Models\Estado;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Service Interface to Implementation
        $this->app->singleton(CopomexServiceInterface::class, CopomexService::class);

        // Bind Repository Interface to Implementation  
        $this->app->singleton(EstadoRepositoryInterface::class, function ($app) {
            return new EstadoRepository(new Estado());
        });

        // Register Console Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                //\App\Console\Commands\TestCopomexCommand::class,    
                \App\Console\Commands\CargarEstadosCommand::class,
                \App\Console\Commands\LimpiarCacheCopomexCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        // Set default string length for MySQL
        Schema::defaultStringLength(191);

        // Set timezone if configured
        if (config('app.timezone')) {
            date_default_timezone_set(config('app.timezone'));
        }
    }
}