<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ParcelaController;
use App\Http\Controllers\CultivoController;
use App\Http\Controllers\PlanRotacionController;
use App\Http\Controllers\DetalleRotacionController;
use App\Http\Controllers\EjecucionRotacionController;
use App\Http\Controllers\ClimaController;
use App\Http\Controllers\ReporteController;

// Redirección inicial a login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación
Auth::routes();

// Rutas protegidas
Route::middleware(['auth'])->group(function () {

    // Dashboard principal
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Gestión de Usuarios
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])
        ->name('users.toggle');

    // Gestión de Parcelas
    Route::resource('parcelas', ParcelaController::class);

    // Mapa general de parcelas (NUEVA RUTA)
    Route::get('/parcelas/mapa/general', [ParcelaController::class, 'mapaGeneral'])
        ->name('parcelas.mapa-general');

    // Gestión de Cultivos
    Route::resource('cultivos', CultivoController::class);

    // ✅ CORREGIDO: Planes de rotación - Rutas manuales
    Route::get('planes', [PlanRotacionController::class, 'index'])->name('planes.index');
    Route::get('planes/create', [PlanRotacionController::class, 'create'])->name('planes.create');
    Route::post('planes', [PlanRotacionController::class, 'store'])->name('planes.store');
    Route::get('planes/{plan_id}', [PlanRotacionController::class, 'show'])->name('planes.show');
    Route::get('planes/{plan_id}/edit', [PlanRotacionController::class, 'edit'])->name('planes.edit');
    Route::put('planes/{plan_id}', [PlanRotacionController::class, 'update'])->name('planes.update');
    Route::delete('planes/{plan_id}', [PlanRotacionController::class, 'destroy'])->name('planes.destroy');

    // ✅ NUEVA RUTA: Vista visual de rotación
    Route::get('planes/{plan_id}/visual', [PlanRotacionController::class, 'visual'])->name('planes.visual');

    // ✅ CORREGIDO: Detalles de rotación - Usar {plan_id}
    Route::get('planes/{plan_id}/detalles/create', [DetalleRotacionController::class, 'create'])
        ->name('detalles.create');
    Route::post('planes/{plan_id}/detalles', [DetalleRotacionController::class, 'store'])
        ->name('detalles.store');
    Route::get('detalles/{detalle}/edit', [DetalleRotacionController::class, 'edit'])
        ->name('detalles.edit');
    Route::put('detalles/{detalle}', [DetalleRotacionController::class, 'update'])
        ->name('detalles.update');

    // Ejecuciones reales de un detalle
    Route::get('detalles/{detalle}/ejecucion/create', [EjecucionRotacionController::class, 'create'])
        ->name('ejecuciones.create');
    Route::post('detalles/{detalle}/ejecucion', [EjecucionRotacionController::class, 'store'])
        ->name('ejecuciones.store');

    // Rutas del módulo de Clima
    Route::get('/clima', [ClimaController::class, 'index'])->name('clima.index');
    Route::get('/clima/historico', [ClimaController::class, 'historico'])->name('clima.historico');
    Route::get('/clima/alertas', [ClimaController::class, 'alertas'])->name('clima.alertas');
    Route::post('/clima/ubicacion', [ClimaController::class, 'obtenerPorUbicacion'])->name('clima.ubicacion');

    // Rutas del módulo de Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/parcelas', [ReporteController::class, 'parcelas'])->name('reportes.parcelas');
        Route::get('/cultivos', [ReporteController::class, 'cultivos'])->name('reportes.cultivos');
        Route::get('/rotaciones', [ReporteController::class, 'rotaciones'])->name('reportes.rotaciones');
        Route::get('/ejecuciones', [ReporteController::class, 'ejecuciones'])->name('reportes.ejecuciones');
        Route::post('/generar-pdf', [ReporteController::class, 'generarPDF'])->name('reportes.generar-pdf');
        Route::get('/datos-grafico', [ReporteController::class, 'obtenerDatosGrafico'])->name('reportes.datos-grafico');
    });
});