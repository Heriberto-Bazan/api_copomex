<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstadosController;

// Ruta raíz - redirigir a estados
Route::get('/', function () {
    return redirect()->route('estados.index');
});

// Grupo de rutas para Estados
Route::prefix('estados')->name('estados.')->group(function () {
    
    // Ruta principal - Vista con DataTables
    Route::get('/', [EstadosController::class, 'index'])->name('index');
    
    // AJAX Routes para funcionalidad dinámica
    Route::post('/cargar', [EstadosController::class, 'cargarEstados'])->name('cargar');
    Route::get('/datatable', [EstadosController::class, 'datatable'])->name('datatable');
    Route::post('/limpiar-cache', [EstadosController::class, 'limpiarCache'])->name('limpiar-cache');
    
    // Ruta para obtener municipios de un estado
    Route::get('/{estado}/municipios', [EstadosController::class, 'municipios'])
        ->name('municipios')
        ->where('estado', '[A-Za-záéíóúñü0-9\s\-_]+');
    
});

// API Routes (opcional - para consumo externo)
Route::prefix('api')->name('api.')->group(function () {
    Route::prefix('estados')->name('estados.')->group(function () {
        Route::get('/', [EstadosController::class, 'apiIndex'])->name('index');
        Route::get('/{estado}/municipios', [EstadosController::class, 'municipios'])
            ->name('municipios')
            ->where('estado', '[A-Za-záéíóúñü\s]+');
    });
    
    // Health check endpoint
    Route::get('/health', [EstadosController::class, 'health'])->name('health');
});

// Ruta de salud para monitoreo
Route::get('/health', [EstadosController::class, 'health'])->name('health');
