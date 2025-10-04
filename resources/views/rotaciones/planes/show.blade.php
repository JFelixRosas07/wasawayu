@extends('adminlte::page')

@section('title', 'Detalle del Plan de Rotación')

@section('content_header')
<h1>Detalle del Plan: {{ $plan->nombre }}</h1>
@stop

@section('content')
<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <a href="{{ route('planes.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Planes
        </a>
    </div>

    <div class="btn-group">
        <a href="{{ route('planes.visual', $plan->id) }}" class="btn btn-success">
            <i class="fas fa-th-large"></i> Vista Visual
        </a>
        <a href="{{ route('detalles.create', $plan->id) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Detalle
        </a>
    </div>
</div>

<!-- PRIMERA PARTE: Información básica del plan -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Información del Plan</h3>
        <div class="card-tools">
            <span class="badge badge-info">Parcela: {{ $plan->parcela->nombre ?? 'N/A' }}</span>
            <span class="badge badge-secondary">Años: {{ $plan->anios }}</span>
            <span class="badge badge-{{ $plan->estado == 'en_ejecucion' ? 'success' : 'warning' }}">
                Estado: {{ ucfirst($plan->estado) }}
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Parcela:</strong> {{ $plan->parcela->nombre ?? 'N/A' }}</p>
                <p><strong>Agricultor:</strong> {{ $plan->parcela->agricultor->name ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Años planificados:</strong> {{ $plan->anios }}</p>
                <p><strong>Detalles registrados:</strong> {{ $plan->detalles->count() }}</p>
            </div>
        </div>
    </div>
</div>

<!-- SEGUNDA PARTE: Tabla de detalles -->
@if($plan->detalles->isEmpty())
    <div class="card mt-4">
        <div class="card-body">
            <p class="text-muted">No hay detalles registrados para este plan.</p>
        </div>
    </div>
@else
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Detalles del Plan ({{ $plan->detalles->count() }})</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Año</th>
                        <th>Cultivo Planificado</th>
                        <th>Descanso</th>
                        <th>Fechas Planificadas</th>
                        <th>Ejecución Real</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plan->detalles as $detalle)
                        @php
                            $ejecucion = $detalle->ejecuciones->first();
                            $tieneEjecucion = $ejecucion !== null;
                        @endphp
                        <tr>
                            <td>{{ $detalle->anio }}</td>
                            <td>
                                @if($detalle->es_descanso)
                                    <span class="badge badge-secondary">Descanso</span>
                                @else
                                    {{ $detalle->cultivo->nombre ?? 'N/A' }}
                                @endif
                            </td>
                            <td>{{ $detalle->es_descanso ? 'Sí' : 'No' }}</td>
                            <td>
                                @if($detalle->fecha_inicio && $detalle->fecha_fin)
                                    {{ \Carbon\Carbon::parse($detalle->fecha_inicio)->format('d/m/Y') }} -
                                    {{ \Carbon\Carbon::parse($detalle->fecha_fin)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">No definidas</span>
                                @endif
                            </td>
                            <td>
                                @if($tieneEjecucion)
                                    <div class="ejecucion-info">
                                        <small>
                                            <strong>Siembra:</strong>
                                            {{ $ejecucion->fecha_siembra->format('d/m/Y') }}<br>
                                            @if($ejecucion->fecha_cosecha)
                                                <strong>Cosecha:</strong>
                                                {{ $ejecucion->fecha_cosecha->format('d/m/Y') }}
                                            @else
                                                <span class="text-warning">En proceso</span>
                                            @endif
                                        </small>
                                    </div>
                                @else
                                    <span class="text-muted">Sin ejecución</span>
                                @endif
                            </td>
                            <td>
                                @if($tieneEjecucion)
                                    @if($ejecucion->estado == 'finalizado')
                                        <span class="badge badge-success">Finalizado</span>
                                    @else
                                        <span class="badge badge-warning">En proceso</span>
                                    @endif
                                @else
                                    <span class="badge badge-secondary">Planificado</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- BOTÓN EDITAR DETALLE -->
                                    <a href="{{ route('detalles.edit', $detalle) }}" class="btn btn-warning"
                                        title="Editar detalle">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <!-- BOTÓN EJECUCIÓN -->
                                    @if($tieneEjecucion)
                                        <!-- SI YA TIENE EJECUCIÓN -->
                                        <button class="btn btn-info" title="Ejecución registrada" disabled>
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    @else
                                        <!-- SI NO TIENE EJECUCIÓN: REGISTRAR -->
                                        <a href="{{ route('ejecuciones.create', $detalle->id) }}" class="btn btn-success"
                                            title="Registrar ejecución real">
                                            <i class="fas fa-play-circle"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

<!-- TERCERA PARTE: Barra de progreso -->
@if(!$plan->detalles->isEmpty())
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-bar"></i> Resumen de Ejecución
        </h5>
    </div>
    <div class="card-body">
        @php
            $totalDetalles = $plan->detalles->count();
            $conEjecucion = $plan->detalles->filter(function($detalle) {
                return $detalle->ejecuciones->isNotEmpty();
            })->count();
            $porcentaje = $totalDetalles > 0 ? ($conEjecucion / $totalDetalles) * 100 : 0;
        @endphp
        
        <div class="progress mb-2" style="height: 25px;">
            <div class="progress-bar bg-success" 
                 role="progressbar" 
                 style="width: {{ $porcentaje }}%"
                 aria-valuenow="{{ $porcentaje }}" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                {{ number_format($porcentaje, 1) }}%
            </div>
        </div>
        <small class="text-muted">
            {{ $conEjecucion }} de {{ $totalDetalles }} años con ejecución registrada
        </small>
    </div>
</div>
@endif
@stop

@section('css')
<style>
    .ejecucion-info {
        font-size: 0.85em;
        line-height: 1.2;
    }
    .progress {
        background-color: #e9ecef;
    }
    .card-tools .badge {
        margin-left: 0.5rem;
    }
</style>
@stop