@extends('adminlte::page')

@section('title', 'Wasawayu - Inicio')

@section('content')
<div class="row">
    <!-- Bienvenida -->
    <div class="col-md-12 mb-4">
        <div class="welcome-card card border-0 shadow-sm bg-light">
            <div class="card-body text-center py-4">
                <div class="icon-wrapper bg-success text-white mb-2">
                    <i class="fas fa-seedling"></i>
                </div>
                <h4 class="fw-bold text-success mb-1">Bienvenido al Sistema</h4>
                <p class="text-muted mb-0">Gestión de Rotación de Cultivos - Monitoreo y Planificación Agrícola</p>
            </div>
        </div>
    </div>

    <!-- KPIs con DATOS REALES -->
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body text-center">
                <i class="fas fa-users text-success fa-2x mb-2"></i>
                <h5 class="fw-bold">{{ $totalAgricultores }}</h5>
                <p class="text-muted mb-0 small">Agricultores</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body text-center">
                <i class="fas fa-map text-primary fa-2x mb-2"></i>
                <h5 class="fw-bold">{{ $totalParcelas }}</h5>
                <p class="text-muted mb-0 small">Parcelas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body text-center">
                <i class="fas fa-leaf text-warning fa-2x mb-2"></i>
                <h5 class="fw-bold">{{ $totalCultivos }}</h5>
                <p class="text-muted mb-0 small">Cultivos</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body text-center">
                <i class="fas fa-sync-alt text-danger fa-2x mb-2"></i>
                <h5 class="fw-bold">{{ $totalRotaciones }}</h5>
                <p class="text-muted mb-0 small">Rotaciones</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .content-wrapper { position: relative; min-height: 85vh; }
    .welcome-card { border-radius: 20px; }
    .icon-wrapper {
        width: 55px; height: 55px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
    }
    .stat-card { border-radius: 15px; transition: 0.3s; }
    .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
    .stat-card h5 { color: #2c3e50; font-size: 1.4rem; }
</style>
@stop