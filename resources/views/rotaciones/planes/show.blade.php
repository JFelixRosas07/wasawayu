@extends('adminlte::page')

@section('title', 'Detalle del Plan de Rotación')

@section('content_header')
    <h1><i class="fas fa-seedling text-success"></i> Detalle del Plan: {{ $plan->nombre }}</h1>
@stop

@section('content')
@php
    use Carbon\Carbon;
    $parcelaId = request('parcela_id');
    $totalDetalles = $plan->detalles->count();
    $finalizados = $plan->detalles->filter(fn($d) => $d->ejecuciones->first()?->fecha_cosecha)->count();
    $avance = $totalDetalles ? round(($finalizados / $totalDetalles) * 100) : 0;
    $esAgricultor = auth()->user()->hasRole('Agricultor');
@endphp

{{-- Botones principales --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('planes.index', $parcelaId ? ['parcela_id' => $parcelaId] : []) }}"
       class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Volver a Planes
    </a>

    {{-- Ocultar "Agregar Detalle" para Agricultor --}}
    @unless($esAgricultor)
        <a href="{{ route('detalles.create', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}"
           class="btn btn-outline-primary">
            <i class="fas fa-plus-circle"></i> Agregar Detalle
        </a>
    @endunless

    {{-- Todos pueden ver la rotación visual --}}
    <a href="{{ route('planes.visual', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}"
       class="btn btn-outline-success">
        <i class="fas fa-map"></i> Rotación Visual
    </a>
</div>

{{-- Resumen del plan --}}
<div class="card mb-4 border-success shadow-sm">
    <div class="card-body">
        <h4 class="text-success mb-2">
            <i class="fas fa-seedling"></i> {{ $plan->nombre }}
        </h4>
        <p class="mb-1">
            <strong>Parcela:</strong> {{ $plan->parcela->nombre ?? 'N/A' }} |
            <strong>Agricultor:</strong> {{ $plan->parcela->agricultor->name ?? 'N/A' }} |
            <strong>Ciclo:</strong> {{ $plan->ciclo ?? 'N/D' }} |
            <strong>Estado:</strong>
            <span class="badge {{ $plan->badge_estado }}">{{ ucfirst($plan->estado_texto) }}</span>
        </p>

        {{-- Barra de progreso --}}
        @if($totalDetalles)
            <div class="progress mt-3" style="height: 20px;">
                <div class="progress-bar bg-success" role="progressbar"
                     style="width: {{ $avance }}%;"
                     aria-valuenow="{{ $avance }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $avance }}%
                </div>
            </div>
            <small class="text-muted">
                Avance del plan ({{ $finalizados }} de {{ $totalDetalles }} años completados)
            </small>
        @else
            <p class="text-muted mt-3 mb-0"><em>Sin detalles registrados aún.</em></p>
        @endif
    </div>
</div>

{{-- Tabla de detalles --}}
@if($plan->detalles->isEmpty())
    <div class="card border-light shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-exclamation-circle fa-2x text-muted mb-3"></i>
            <p class="text-muted">No hay detalles registrados para este plan.</p>

            {{-- Ocultar "Agregar Primer Detalle" para Agricultor --}}
            @unless($esAgricultor)
                <a href="{{ route('detalles.create', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}"
                   class="btn btn-primary">
                   <i class="fas fa-plus"></i> Agregar Primer Detalle
                </a>
            @endunless
        </div>
    </div>
@else
    <div class="card mt-3 shadow-sm">
        <div class="card-header bg-light">
            <h3 class="card-title">
                <i class="fas fa-clipboard-list text-primary"></i> Detalles del Plan
            </h3>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 8%">Año</th>
                        <th style="width: 25%">Cultivo</th>
                        <th style="width: 10%">Descanso</th>
                        <th style="width: 25%">Fechas</th>
                        <th style="width: 12%">Ejecución</th>
                        {{-- Ocultar columna "Acciones" si es Agricultor --}}
                        @unless($esAgricultor)
                            <th style="width: 20%">Acciones</th>
                        @endunless
                    </tr>
                </thead>
                <tbody>
                    @foreach($plan->detalles->sortBy('anio') as $detalle)
                        @php
                            $ejecucion = $detalle->ejecuciones->first();
                            $hoy = Carbon::today();
                            $inicio = $detalle->fecha_inicio ? Carbon::parse($detalle->fecha_inicio) : null;
                            $fin = $detalle->fecha_fin ? Carbon::parse($detalle->fecha_fin) : null;
                        @endphp
                        <tr>
                            <td><b>Año {{ $detalle->anio }}</b></td>
                            <td>
                                @if($detalle->es_descanso)
                                    <span class="badge badge-secondary">Descanso</span>
                                @else
                                    {{ $detalle->cultivo->nombre ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">
                                        {{ ucfirst($detalle->cultivo->categoria ?? '') }}
                                    </small>
                                @endif
                            </td>
                            <td>{{ $detalle->es_descanso ? 'Sí' : 'No' }}</td>
                            <td>
                                @if($detalle->fecha_inicio && $detalle->fecha_fin)
                                    <span class="text-success">
                                        {{ \Carbon\Carbon::parse($detalle->fecha_inicio)->format('d/m/Y') }}
                                        –
                                        {{ \Carbon\Carbon::parse($detalle->fecha_fin)->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">No definidas</span>
                                @endif
                            </td>
                            <td>
                                @if($ejecucion && $ejecucion->fecha_cosecha)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Finalizado
                                    </span>
                                @elseif($inicio && $fin && $hoy->between($inicio, $fin))
                                    <span class="badge badge-warning">
                                        <i class="fas fa-hourglass-half"></i> En ejecución
                                    </span>
                                @elseif($inicio && $hoy->lt($inicio))
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-calendar-alt"></i> Planificado
                                    </span>
                                @elseif($fin && $hoy->gt($fin))
                                    <span class="badge badge-info">
                                        <i class="fas fa-check-circle"></i> Pendiente de cierre
                                    </span>
                                @else
                                    <span class="badge badge-light">
                                        <i class="fas fa-question-circle"></i> Sin datos
                                    </span>
                                @endif
                            </td>

                            {{-- Ocultar acciones para Agricultor --}}
                            @unless($esAgricultor)
                                <td class="text-nowrap">
                                    <a href="{{ route('detalles.edit', ['detalle' => $detalle->id, 'parcela_id' => $parcelaId]) }}"
                                       class="btn btn-sm btn-warning" title="Editar detalle">
                                       <i class="fas fa-edit"></i>
                                    </a>

                                    @if(!$ejecucion)
                                        <a href="{{ route('ejecuciones.create', ['detalle' => $detalle->id, 'parcela_id' => $parcelaId]) }}"
                                           class="btn btn-sm btn-success" title="Registrar ejecución">
                                           <i class="fas fa-play"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('ejecuciones.edit', $ejecucion->id) }}"
                                           class="btn btn-sm btn-info" title="Editar ejecución">
                                           <i class="fas fa-sync-alt"></i>
                                        </a>
                                    @endif
                                </td>
                            @endunless
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Alertas del plan --}}
@if($plan->detalles->flatMap->alertas->count())
    @php
        $alertas = $plan->detalles->flatMap->alertas;
        $altas = $alertas->where('severidad', 'alta')->count();
        $medias = $alertas->where('severidad', 'media')->count();
        $informativas = $alertas->where('severidad', 'ninguna')->count();
    @endphp

    <div class="card mt-4 border-warning shadow-sm">
        <div class="card-header bg-warning-subtle d-flex justify-content-between align-items-center">
            <div>
                <strong><i class="fas fa-bell text-warning"></i> Alertas de Rotación</strong>
            </div>
            <small class="text-dark">
                <i class="fas fa-exclamation-triangle text-danger"></i> Altas: {{ $altas }} |
                <i class="fas fa-exclamation-circle text-warning"></i> Medias: {{ $medias }} |
                <i class="fas fa-info-circle text-secondary"></i> Informativas: {{ $informativas }}
            </small>
        </div>

        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-success text-white">
                    <tr>
                        <th style="width: 10%">Año</th>
                        <th style="width: 25%">Tipo</th>
                        <th style="width: 50%">Descripción</th>
                        <th style="width: 15%">Severidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plan->detalles->sortBy('anio') as $detalle)
                        @foreach($detalle->alertas as $alerta)
                            <tr class="
                                @if($alerta->severidad == 'alta') table-danger
                                @elseif($alerta->severidad == 'media') table-warning
                                @elseif($alerta->severidad == 'ninguna') table-light
                                @endif
                            ">
                                <td><strong>Año {{ $detalle->anio }}</strong></td>

                                <td>
                                    @switch($alerta->tipo_alerta)
                                        @case('cultivo_repetido')
                                            <i class="fas fa-redo text-danger"></i> Cultivo repetido
                                            @break
                                        @case('misma_familia')
                                            <i class="fas fa-seedling text-danger"></i> Misma familia botánica
                                            @break
                                        @case('alta_demanda_consecutiva')
                                            <i class="fas fa-fire text-danger"></i> Alta demanda consecutiva
                                            @break
                                        @case('sin_leguminosa')
                                            <i class="fas fa-leaf text-warning"></i> Sin leguminosa
                                            @break
                                        @case('sin_descanso_prolongado')
                                            <i class="fas fa-bed text-danger"></i> Sin descanso prolongado
                                            @break
                                        @case('descanso_programado')
                                            <i class="fas fa-moon text-secondary"></i> Descanso programado
                                            @break
                                        @default
                                            <i class="fas fa-info-circle text-muted"></i> {{ ucfirst(str_replace('_', ' ', $alerta->tipo_alerta)) }}
                                    @endswitch
                                </td>

                                <td class="text-start">{{ $alerta->descripcion }}</td>

                                <td>
                                    @switch($alerta->severidad)
                                        @case('alta')
                                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle"></i> Alta</span>
                                            @break
                                        @case('media')
                                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle"></i> Media</span>
                                            @break
                                        @case('ninguna')
                                            <span class="badge bg-secondary"><i class="fas fa-info-circle"></i> Informativa</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark">N/A</span>
                                    @endswitch
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@stop
