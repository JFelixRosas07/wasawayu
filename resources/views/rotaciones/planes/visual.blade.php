@extends('adminlte::page')

@section('title', 'Vista Visual de Rotación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="mb-2">
            <i class="fas fa-sync-alt text-success me-2"></i>
            Rotación Visual: {{ $plan->nombre }}
        </h1>
        <div class="btn-group mb-2">
            <a href="{{ route('planes.show', $plan->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-table"></i> Vista Tabla
            </a>
            <a href="{{ route('planes.visual', $plan->id) }}" class="btn btn-success">
                <i class="fas fa-th-large"></i> Vista Visual
            </a>
            <a href="{{ route('detalles.create', $plan->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Agregar Detalle
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">

        <!-- Información del Plan -->
        <div class="info-card mb-4">
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="stat-box">
                        <h4>{{ $plan->parcela->nombre ?? 'N/A' }}</h4>
                        <small>Parcela</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="stat-box">
                        <h4>{{ $plan->anios ?? $plan->detalles->count() }}</h4>
                        <small>Años Planificados</small>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-3 mb-md-0">
                    <div class="stat-box">
                        <h4>{{ $plan->detalles->count() }}</h4>
                        <small>Detalles Registrados</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-box">
                        @php
                            $ejecutados = $plan->detalles->filter(fn($d) => $d->ejecuciones->isNotEmpty())->count();
                            $total = $plan->detalles->count();
                            $porcentaje = $total > 0 ? ($ejecutados / $total) * 100 : 0;
                        @endphp
                        <h4 class="{{ $porcentaje == 100 ? 'text-success' : ($porcentaje > 0 ? 'text-warning' : 'text-secondary') }}">
                            {{ number_format($porcentaje, 0) }}%
                        </h4>
                        <small>Ejecutado</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista Visual de Rotación -->
        <div class="rotacion-visual-container mx-auto">
            <h3 class="text-center mb-4">
                <i class="fas fa-seedling me-2 text-success"></i>
                Ciclo de Rotación - {{ $plan->parcela->nombre ?? 'Parcela' }}
            </h3>

            <div class="rotacion-grid">

                @foreach ([1,2,3,4] as $anio)
                    @php $detalle = $plan->detalles->where('anio', $anio)->first(); @endphp
                    <div class="rotacion-item item-{{ $anio }}" data-anio="{{ $anio }}">
                        <div class="rotacion-content {{ $detalle ? 'has-content' : 'empty' }}">
                            @if($detalle)
                                <div class="cultivo-imagen">
                                    @if($detalle->es_descanso)
                                        <div class="descanso-visual">
                                            <i class="fas fa-moon fa-3x"></i>
                                            <div class="descanso-text">Descanso</div>
                                        </div>
                                    @elseif($detalle->cultivo && $detalle->cultivo->imagen)
                                        <img src="{{ asset($detalle->cultivo->imagen) }}" alt="{{ $detalle->cultivo->nombre }}" 
                                            class="cultivo-img-real"
                                            onerror="this.src='{{ asset('images/placeholder-cultivo.jpg') }}'">
                                    @else
                                        <div class="cultivo-placeholder">
                                            <i class="fas fa-seedling fa-2x"></i>
                                            <div class="placeholder-text">{{ $detalle->cultivo->nombre ?? 'Sin imagen' }}</div>
                                        </div>
                                    @endif
                                </div>

                                <div class="cultivo-info">
                                    <h5>Año {{ $anio }}</h5>
                                    <p class="cultivo-nombre">
                                        @if($detalle->es_descanso)
                                            <span class="badge badge-descanso">Descanso de Suelo</span>
                                        @else
                                            {{ $detalle->cultivo->nombre ?? 'N/A' }}
                                        @endif
                                    </p>
                                    @if(!$detalle->es_descanso && $detalle->cultivo)
                                        <div class="cultivo-meta">
                                            <small class="categoria {{ strtolower($detalle->cultivo->categoria) }}">
                                                {{ $detalle->cultivo->categoria }}
                                            </small>
                                            <small class="carga-suelo {{ strtolower($detalle->cultivo->cargaSuelo) }}">
                                                {{ $detalle->cultivo->cargaSuelo }}
                                            </small>
                                        </div>
                                    @endif
                                    <small class="text-muted fechas d-block">
                                        @if($detalle->fecha_inicio && $detalle->fecha_fin)
                                            {{ $detalle->fecha_inicio->format('M Y') }} - {{ $detalle->fecha_fin->format('M Y') }}
                                        @else
                                            Fechas no definidas
                                        @endif
                                    </small>
                                </div>

                                @if($detalle->ejecuciones->isNotEmpty())
                                    <div class="ejecucion-badge" title="Ejecución registrada">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="badge-text">Ejecutado</span>
                                    </div>
                                @endif
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-plus-circle fa-2x"></i>
                                    <p>Año {{ $anio }}</p>
                                    <small>Sin planificar</small>
                                </div>
                            @endif
                        </div>

                        @if($detalle)
                            <div class="rotacion-actions">
                                <a href="{{ route('detalles.edit', $detalle->id) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!$detalle->ejecuciones->isNotEmpty())
                                    <a href="{{ route('ejecuciones.create', $detalle->id) }}" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-play"></i>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach

                <!-- Centro -->
                <div class="rotacion-center">
                    <div class="center-content">
                        <i class="fas fa-sync-alt fa-3x mb-2"></i>
                        <h5>Rotación</h5>
                        <small>{{ $plan->parcela->nombre ?? 'Parcela' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Años adicionales -->
        @if($plan->detalles->where('anio', '>', 4)->count() > 0)
            <div class="card mt-5">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-ellipsis-h"></i> Años Adicionales</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($plan->detalles->where('anio', '>', 4) as $detalle)
                            <div class="col-md-3 mb-3">
                                <div class="additional-year text-center">
                                    <h6>Año {{ $detalle->anio }}</h6>
                                    <p class="mb-1">
                                        @if($detalle->es_descanso)
                                            <span class="badge badge-secondary">Descanso</span>
                                        @else
                                            {{ $detalle->cultivo->nombre ?? 'N/A' }}
                                        @endif
                                    </p>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('detalles.edit', $detalle->id) }}" class="btn btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$detalle->ejecuciones->isNotEmpty())
                                            <a href="{{ route('ejecuciones.create', $detalle->id) }}" class="btn btn-outline-success">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
<style>
    /* Mejora del contraste en info-card */
    .info-card {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.3);
        padding: 1.5rem;
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }

    .stat-box h4 {
        font-weight: 700;
        color: var(--andino-oscuro);
    }

    .stat-box small {
        color: #2b2b2b !important; /* texto más visible */
        font-weight: 500;
    }

    .text-muted {
        color: #444 !important; /* mejora contraste general */
    }

    /* resto igual que antes */
    .rotacion-visual-container {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.3);
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(27,67,50,0.15);
    }

    .rotacion-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 30px;
        position: relative;
        min-height: 480px;
    }

    .rotacion-item {
        position: relative;
        transition: transform 0.3s ease;
    }

    .rotacion-content {
        background: rgba(255,255,255,0.25);
        border-radius: 16px;
        border: 2px solid rgba(255,255,255,0.4);
        padding: 1.5rem;
        transition: all 0.3s ease;
        height: 100%;
    }

    .rotacion-content.has-content:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(27,67,50,0.15);
    }

    .cultivo-imagen {
        text-align: center;
        height: 120px;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 1rem;
    }

    .cultivo-img-real {
        max-width: 100%;
        max-height: 100px;
        border-radius: 12px;
        object-fit: cover;
    }

    .descanso-visual { color: #6c757d; text-align: center; }
    .descanso-text { font-size: 0.9rem; font-weight: 600; }

    .cultivo-info h5 { font-weight: 700; color: var(--andino-oscuro); }
    .cultivo-nombre { font-weight: 600; color: var(--andino-oscuro); margin-bottom: 0.5rem; }

    .categoria, .carga-suelo {
        display: inline-block;
        padding: 0.2rem 0.6rem;
        border-radius: 10px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 0.3rem;
    }

    .categoria.tubérculo { background:#e3f2fd;color:#1976d2; }
    .categoria.leguminosa { background:#e8f5e8;color:#2e7d32; }
    .categoria.cereal { background:#fff3e0;color:#ef6c00; }
    .categoria.hortaliza { background:#fce4ec;color:#c2185b; }

    .carga-suelo.alta { background:#ffebee;color:#c62828; }
    .carga-suelo.media { background:#fff3e0;color:#ef6c00; }
    .carga-suelo.baja { background:#e8f5e8;color:#2e7d32; }
    .carga-suelo.regenerativa { background:#e3f2fd;color:#1565c0; }

    .badge-descanso {
        background: linear-gradient(135deg,#6c757d,#495057);
        color:white;
        border-radius: 20px;
        padding:0.3rem 0.8rem;
    }

    .ejecucion-badge {
        position: absolute;
        top: 10px; right: 10px;
        background: rgba(40,167,69,0.9);
        color: white;
        border-radius: 15px;
        padding: 0.3rem 0.6rem;
        font-size: 0.75rem;
        display: flex; align-items: center; gap: 0.2rem;
    }

    .rotacion-center {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
    }

    .center-content {
        background: linear-gradient(135deg,var(--andino-oscuro),var(--andino-hoja));
        color: white;
        border-radius: 50%;
        width: 140px; height: 140px;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        text-align: center;
        box-shadow: 0 10px 30px rgba(27,67,50,0.25);
    }

    .rotacion-actions {
        position: absolute;
        bottom: -20px; left: 50%;
        transform: translateX(-50%);
        opacity: 0; transition: opacity 0.3s ease;
    }

    .rotacion-item:hover .rotacion-actions { opacity: 1; }

    /* Responsive */
    @media (max-width: 768px) {
        .rotacion-grid {
            grid-template-columns: 1fr;
            grid-template-rows: auto;
        }
        .rotacion-center {
            position: relative;
            transform: none;
            margin: 2rem auto;
        }
    }
</style>
@stop
