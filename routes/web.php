<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controladores principales
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ParcelaController;
use App\Http\Controllers\CultivoController;
use App\Http\Controllers\PlanRotacionController;
use App\Http\Controllers\DetalleRotacionController;
use App\Http\Controllers\EjecucionRotacionController;
use App\Http\Controllers\ClimaController;
use App\Http\Controllers\ReporteController;

// Controladores adicionales del Dashboard de Rotaciones
use App\Http\Controllers\DashboardRotacionController;
use App\Http\Controllers\ParcelaGeoJsonController;

// Redirección inicial al login
Route::get('/', fn() => redirect()->route('login'));

// Rutas de autenticación
Auth::routes();

// Rutas protegidas (solo usuarios autenticados)
Route::middleware(['auth'])->group(function () {

    // Módulo principal: Dashboard
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Módulo de usuarios
    Route::middleware(['role:Administrador'])->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
    });

    // Perfil de usuario
    Route::get('/perfil', [UserController::class, 'perfil'])->name('perfil');
    Route::put('/perfil', [UserController::class, 'actualizarPerfil'])->name('perfil.update');

    // Módulo de parcelas
    Route::resource('parcelas', ParcelaController::class);
    Route::get('/parcelas/mapa/general', [ParcelaController::class, 'mapaGeneral'])->name('parcelas.mapa-general');

    // Módulo de cultivos
    Route::resource('cultivos', CultivoController::class);

    // Módulo de rotaciones (planes, detalles y ejecuciones)
    Route::prefix('admin/rotaciones')
        ->name('admin.rotaciones.')
        ->middleware('role:Administrador|TecnicoAgronomo|Agricultor')
        ->group(function () {
            Route::get('/dashboard', [DashboardRotacionController::class, 'index'])->name('dashboard');
            Route::get('/parcelas/geojson', [ParcelaGeoJsonController::class, 'geojson'])->name('parcelas.geojson');
        });

    // Planes de rotación
    Route::get('planes', [PlanRotacionController::class, 'index'])->name('planes.index');
    Route::get('planes/create', [PlanRotacionController::class, 'create'])->name('planes.create');
    Route::post('planes', [PlanRotacionController::class, 'store'])->name('planes.store');
    Route::get('planes/{plan_id}', [PlanRotacionController::class, 'show'])->name('planes.show');
    Route::get('planes/{plan_id}/edit', [PlanRotacionController::class, 'edit'])->name('planes.edit');
    Route::put('planes/{plan_id}', [PlanRotacionController::class, 'update'])->name('planes.update');
    Route::delete('planes/{plan_id}', [PlanRotacionController::class, 'destroy'])->name('planes.destroy');
    Route::get('planes/{plan_id}/visual', [PlanRotacionController::class, 'visual'])->name('planes.visual');

    // Detalles de rotación
    Route::get('planes/{plan_id}/detalles/create', [DetalleRotacionController::class, 'create'])->name('detalles.create');
    Route::post('planes/{plan_id}/detalles', [DetalleRotacionController::class, 'store'])->name('detalles.store');
    Route::get('detalles/{detalle}/edit', [DetalleRotacionController::class, 'edit'])->name('detalles.edit');
    Route::put('detalles/{detalle}', [DetalleRotacionController::class, 'update'])->name('detalles.update');

    // Módulo de ejecuciones de rotación
    Route::get('detalles/{detalle}/ejecucion/create', [EjecucionRotacionController::class, 'create'])->name('ejecuciones.create');
    Route::post('detalles/{detalle}/ejecucion', [EjecucionRotacionController::class, 'store'])->name('ejecuciones.store');
    Route::get('ejecuciones/{id}/edit', [EjecucionRotacionController::class, 'edit'])->name('ejecuciones.edit');
    Route::put('ejecuciones/{id}', [EjecucionRotacionController::class, 'update'])->name('ejecuciones.update');

    // Módulo de clima
    Route::get('/clima', [ClimaController::class, 'index'])->name('clima.index');
    Route::get('/clima/historico', [ClimaController::class, 'historico'])->name('clima.historico');
    Route::get('/clima/alertas', [ClimaController::class, 'alertas'])->name('clima.alertas');
    Route::post('/clima/ubicacion', [ClimaController::class, 'obtenerPorUbicacion'])->name('clima.ubicacion');

    // Módulo de reportes
    Route::prefix('reportes')
        ->middleware('role:Administrador|TecnicoAgronomo|Agricultor')
        ->group(function () {

            // Página principal de reportes
            Route::get('/', [ReporteController::class, 'index'])->name('reportes.index');

            // Reportes principales
            Route::get('/parcelas', [ReporteController::class, 'parcelasAgricultor'])->name('reportes.parcelas.agricultor');
            Route::get('/rotacion', [ReporteController::class, 'rotacionAgricultor'])->name('reportes.rotacion.agricultor');
            Route::get('/cultivos', [ReporteController::class, 'cultivos'])->name('reportes.cultivos.sistema');
            Route::get('/ejecuciones', [ReporteController::class, 'ejecuciones'])->name('reportes.ejecuciones.sistema');

            // Endpoints JSON
            Route::get('/parcelas/{id}', [ReporteController::class, 'parcelasData']);
            Route::get('/planes/{parcela}', [ReporteController::class, 'planesData']);
            Route::get('/detalles/{plan}', [ReporteController::class, 'detallesData']);
            Route::get('/cultivos/data', [ReporteController::class, 'cultivosData'])->name('reportes.cultivos.data');
            Route::get('/ejecuciones/data', [ReporteController::class, 'ejecucionesData'])->name('reportes.ejecuciones.data');
        });

});
