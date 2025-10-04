@extends('adminlte::page')

@section('title', 'Vista Visual de Rotación')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Rotación Visual: {{ $plan->nombre }}</h1>
        <div class="btn-group">
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
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="info-card">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h4>{{ $plan->parcela->nombre ?? 'N/A' }}</h4>
                                <small class="text-muted">Parcela</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h4>{{ $plan->anios }}</h4>
                                <small class="text-muted">Años Planificados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                <h4>{{ $plan->detalles->count() }}</h4>
                                <small class="text-muted">Detalles Registrados</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-box">
                                @php
                                    $ejecutados = $plan->detalles->filter(function($detalle) {
                                        return $detalle->ejecuciones->isNotEmpty();
                                    })->count();
                                    $total = $plan->detalles->count();
                                    $porcentaje = $total > 0 ? ($ejecutados / $total) * 100 : 0;
                                @endphp
                                <h4 class="{{ $porcentaje == 100 ? 'text-success' : ($porcentaje > 0 ? 'text-warning' : 'text-secondary') }}">
                                    {{ number_format($porcentaje, 0) }}%
                                </h4>
                                <small class="text-muted">Ejecutado</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista Visual de Rotación -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="rotacion-visual-container">
                    <div class="rotacion-title">
                        <h3 class="text-center mb-4">
                            <i class="fas fa-sync-alt me-2"></i>
                            Ciclo de Rotación - {{ $plan->parcela->nombre ?? 'Parcela' }}
                        </h3>
                    </div>
                    
                    <div class="rotacion-grid">
                        <!-- Año 1 - Esquina Superior Izquierda -->
                        @php $detalle1 = $plan->detalles->where('anio', 1)->first(); @endphp
                        <div class="rotacion-item item-1" data-anio="1">
                            <div class="rotacion-content {{ $detalle1 ? 'has-content' : 'empty' }}">
                                @if($detalle1)
                                    <div class="cultivo-imagen">
                                        @if($detalle1->es_descanso)
                                            <div class="descanso-visual">
                                                <i class="fas fa-moon fa-3x"></i>
                                                <div class="descanso-text">Descanso</div>
                                            </div>
                                        @else
                                            @if($detalle1->cultivo && $detalle1->cultivo->imagen)
                                                <img src="{{ asset($detalle1->cultivo->imagen) }}" 
                                                     alt="{{ $detalle1->cultivo->nombre }}"
                                                     class="cultivo-img-real"
                                                     onerror="this.src='{{ asset('images/placeholder-cultivo.jpg') }}'">
                                            @else
                                                <div class="cultivo-placeholder">
                                                    <i class="fas fa-seedling fa-2x"></i>
                                                    <div class="placeholder-text">{{ $detalle1->cultivo->nombre ?? 'Sin imagen' }}</div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="cultivo-info">
                                        <h5>Año 1</h5>
                                        <p class="cultivo-nombre">
                                            @if($detalle1->es_descanso)
                                                <span class="badge badge-descanso">Descanso de Suelo</span>
                                            @else
                                                {{ $detalle1->cultivo->nombre ?? 'N/A' }}
                                            @endif
                                        </p>
                                        <div class="cultivo-meta">
                                            @if(!$detalle1->es_descanso && $detalle1->cultivo)
                                                <small class="categoria {{ strtolower($detalle1->cultivo->categoria) }}">
                                                    {{ $detalle1->cultivo->categoria }}
                                                </small>
                                                <small class="carga-suelo {{ strtolower($detalle1->cultivo->cargaSuelo) }}">
                                                    {{ $detalle1->cultivo->cargaSuelo }}
                                                </small>
                                            @endif
                                        </div>
                                        <small class="text-muted fechas">
                                            @if($detalle1->fecha_inicio && $detalle1->fecha_fin)
                                                {{ $detalle1->fecha_inicio->format('M Y') }} - {{ $detalle1->fecha_fin->format('M Y') }}
                                            @else
                                                Fechas no definidas
                                            @endif
                                        </small>
                                    </div>
                                    @if($detalle1->ejecuciones->isNotEmpty())
                                        <div class="ejecucion-badge" title="Ejecución registrada">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="badge-text">Ejecutado</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-plus-circle fa-2x"></i>
                                        <p>Año 1</p>
                                        <small>Sin planificar</small>
                                    </div>
                                @endif
                            </div>
                            @if($detalle1)
                                <div class="rotacion-actions">
                                    <a href="{{ route('detalles.edit', $detalle1->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$detalle1->ejecuciones->isNotEmpty())
                                        <a href="{{ route('ejecuciones.create', $detalle1->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Año 2 - Esquina Superior Derecha -->
                        @php $detalle2 = $plan->detalles->where('anio', 2)->first(); @endphp
                        <div class="rotacion-item item-2" data-anio="2">
                            <div class="rotacion-content {{ $detalle2 ? 'has-content' : 'empty' }}">
                                @if($detalle2)
                                    <div class="cultivo-imagen">
                                        @if($detalle2->es_descanso)
                                            <div class="descanso-visual">
                                                <i class="fas fa-moon fa-3x"></i>
                                                <div class="descanso-text">Descanso</div>
                                            </div>
                                        @else
                                            @if($detalle2->cultivo && $detalle2->cultivo->imagen)
                                                <img src="{{ asset($detalle2->cultivo->imagen) }}" 
                                                     alt="{{ $detalle2->cultivo->nombre }}"
                                                     class="cultivo-img-real"
                                                     onerror="this.src='{{ asset('images/placeholder-cultivo.jpg') }}'">
                                            @else
                                                <div class="cultivo-placeholder">
                                                    <i class="fas fa-seedling fa-2x"></i>
                                                    <div class="placeholder-text">{{ $detalle2->cultivo->nombre ?? 'Sin imagen' }}</div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="cultivo-info">
                                        <h5>Año 2</h5>
                                        <p class="cultivo-nombre">
                                            @if($detalle2->es_descanso)
                                                <span class="badge badge-descanso">Descanso de Suelo</span>
                                            @else
                                                {{ $detalle2->cultivo->nombre ?? 'N/A' }}
                                            @endif
                                        </p>
                                        <div class="cultivo-meta">
                                            @if(!$detalle2->es_descanso && $detalle2->cultivo)
                                                <small class="categoria {{ strtolower($detalle2->cultivo->categoria) }}">
                                                    {{ $detalle2->cultivo->categoria }}
                                                </small>
                                                <small class="carga-suelo {{ strtolower($detalle2->cultivo->cargaSuelo) }}">
                                                    {{ $detalle2->cultivo->cargaSuelo }}
                                                </small>
                                            @endif
                                        </div>
                                        <small class="text-muted fechas">
                                            @if($detalle2->fecha_inicio && $detalle2->fecha_fin)
                                                {{ $detalle2->fecha_inicio->format('M Y') }} - {{ $detalle2->fecha_fin->format('M Y') }}
                                            @else
                                                Fechas no definidas
                                            @endif
                                        </small>
                                    </div>
                                    @if($detalle2->ejecuciones->isNotEmpty())
                                        <div class="ejecucion-badge" title="Ejecución registrada">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="badge-text">Ejecutado</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-plus-circle fa-2x"></i>
                                        <p>Año 2</p>
                                        <small>Sin planificar</small>
                                    </div>
                                @endif
                            </div>
                            @if($detalle2)
                                <div class="rotacion-actions">
                                    <a href="{{ route('detalles.edit', $detalle2->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$detalle2->ejecuciones->isNotEmpty())
                                        <a href="{{ route('ejecuciones.create', $detalle2->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Centro (Título de la parcela) -->
                        <div class="rotacion-center">
                            <div class="center-content">
                                <i class="fas fa-sync-alt fa-3x mb-3"></i>
                                <h4>Rotación</h4>
                                <small class="text-muted">{{ $plan->parcela->nombre ?? 'Parcela' }}</small>
                            </div>
                        </div>

                        <!-- Año 3 - Esquina Inferior Derecha -->
                        @php $detalle3 = $plan->detalles->where('anio', 3)->first(); @endphp
                        <div class="rotacion-item item-3" data-anio="3">
                            <div class="rotacion-content {{ $detalle3 ? 'has-content' : 'empty' }}">
                                @if($detalle3)
                                    <div class="cultivo-imagen">
                                        @if($detalle3->es_descanso)
                                            <div class="descanso-visual">
                                                <i class="fas fa-moon fa-3x"></i>
                                                <div class="descanso-text">Descanso</div>
                                            </div>
                                        @else
                                            @if($detalle3->cultivo && $detalle3->cultivo->imagen)
                                                <img src="{{ asset($detalle3->cultivo->imagen) }}" 
                                                     alt="{{ $detalle3->cultivo->nombre }}"
                                                     class="cultivo-img-real"
                                                     onerror="this.src='{{ asset('images/placeholder-cultivo.jpg') }}'">
                                            @else
                                                <div class="cultivo-placeholder">
                                                    <i class="fas fa-seedling fa-2x"></i>
                                                    <div class="placeholder-text">{{ $detalle3->cultivo->nombre ?? 'Sin imagen' }}</div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="cultivo-info">
                                        <h5>Año 3</h5>
                                        <p class="cultivo-nombre">
                                            @if($detalle3->es_descanso)
                                                <span class="badge badge-descanso">Descanso de Suelo</span>
                                            @else
                                                {{ $detalle3->cultivo->nombre ?? 'N/A' }}
                                            @endif
                                        </p>
                                        <div class="cultivo-meta">
                                            @if(!$detalle3->es_descanso && $detalle3->cultivo)
                                                <small class="categoria {{ strtolower($detalle3->cultivo->categoria) }}">
                                                    {{ $detalle3->cultivo->categoria }}
                                                </small>
                                                <small class="carga-suelo {{ strtolower($detalle3->cultivo->cargaSuelo) }}">
                                                    {{ $detalle3->cultivo->cargaSuelo }}
                                                </small>
                                            @endif
                                        </div>
                                        <small class="text-muted fechas">
                                            @if($detalle3->fecha_inicio && $detalle3->fecha_fin)
                                                {{ $detalle3->fecha_inicio->format('M Y') }} - {{ $detalle3->fecha_fin->format('M Y') }}
                                            @else
                                                Fechas no definidas
                                            @endif
                                        </small>
                                    </div>
                                    @if($detalle3->ejecuciones->isNotEmpty())
                                        <div class="ejecucion-badge" title="Ejecución registrada">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="badge-text">Ejecutado</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-plus-circle fa-2x"></i>
                                        <p>Año 3</p>
                                        <small>Sin planificar</small>
                                    </div>
                                @endif
                            </div>
                            @if($detalle3)
                                <div class="rotacion-actions">
                                    <a href="{{ route('detalles.edit', $detalle3->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$detalle3->ejecuciones->isNotEmpty())
                                        <a href="{{ route('ejecuciones.create', $detalle3->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Año 4 - Esquina Inferior Izquierda -->
                        @php $detalle4 = $plan->detalles->where('anio', 4)->first(); @endphp
                        <div class="rotacion-item item-4" data-anio="4">
                            <div class="rotacion-content {{ $detalle4 ? 'has-content' : 'empty' }}">
                                @if($detalle4)
                                    <div class="cultivo-imagen">
                                        @if($detalle4->es_descanso)
                                            <div class="descanso-visual">
                                                <i class="fas fa-moon fa-3x"></i>
                                                <div class="descanso-text">Descanso</div>
                                            </div>
                                        @else
                                            @if($detalle4->cultivo && $detalle4->cultivo->imagen)
                                                <img src="{{ asset($detalle4->cultivo->imagen) }}" 
                                                     alt="{{ $detalle4->cultivo->nombre }}"
                                                     class="cultivo-img-real"
                                                     onerror="this.src='{{ asset('images/placeholder-cultivo.jpg') }}'">
                                            @else
                                                <div class="cultivo-placeholder">
                                                    <i class="fas fa-seedling fa-2x"></i>
                                                    <div class="placeholder-text">{{ $detalle4->cultivo->nombre ?? 'Sin imagen' }}</div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="cultivo-info">
                                        <h5>Año 4</h5>
                                        <p class="cultivo-nombre">
                                            @if($detalle4->es_descanso)
                                                <span class="badge badge-descanso">Descanso de Suelo</span>
                                            @else
                                                {{ $detalle4->cultivo->nombre ?? 'N/A' }}
                                            @endif
                                        </p>
                                        <div class="cultivo-meta">
                                            @if(!$detalle4->es_descanso && $detalle4->cultivo)
                                                <small class="categoria {{ strtolower($detalle4->cultivo->categoria) }}">
                                                    {{ $detalle4->cultivo->categoria }}
                                                </small>
                                                <small class="carga-suelo {{ strtolower($detalle4->cultivo->cargaSuelo) }}">
                                                    {{ $detalle4->cultivo->cargaSuelo }}
                                                </small>
                                            @endif
                                        </div>
                                        <small class="text-muted fechas">
                                            @if($detalle4->fecha_inicio && $detalle4->fecha_fin)
                                                {{ $detalle4->fecha_inicio->format('M Y') }} - {{ $detalle4->fecha_fin->format('M Y') }}
                                            @else
                                                Fechas no definidas
                                            @endif
                                        </small>
                                    </div>
                                    @if($detalle4->ejecuciones->isNotEmpty())
                                        <div class="ejecucion-badge" title="Ejecución registrada">
                                            <i class="fas fa-check-circle"></i>
                                            <span class="badge-text">Ejecutado</span>
                                        </div>
                                    @endif
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-plus-circle fa-2x"></i>
                                        <p>Año 4</p>
                                        <small>Sin planificar</small>
                                    </div>
                                @endif
                            </div>
                            @if($detalle4)
                                <div class="rotacion-actions">
                                    <a href="{{ route('detalles.edit', $detalle4->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if(!$detalle4->ejecuciones->isNotEmpty())
                                        <a href="{{ route('ejecuciones.create', $detalle4->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles Adicionales (Años 5+) -->
        @if($plan->detalles->where('anio', '>', 4)->count() > 0)
        <div class="row mt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-ellipsis-h"></i> Años Adicionales
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($plan->detalles->where('anio', '>', 4) as $detalle)
                                <div class="col-md-3 mb-3">
                                    <div class="additional-year">
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
            </div>
        </div>
        @endif
    </div>
@stop

@section('css')
<style>
    /* Estilos específicos para la vista visual de rotación */
    .rotacion-visual-container {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(15px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        padding: 2rem;
        box-shadow: 0 20px 60px rgba(27, 67, 50, 0.1);
    }

    .rotacion-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 30px;
        position: relative;
        min-height: 500px;
    }

    .rotacion-item {
        position: relative;
        transition: all 0.3s ease;
    }

    .rotacion-content {
        background: rgba(255, 255, 255, 0.25);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        border: 2px solid rgba(255, 255, 255, 0.4);
        padding: 1.5rem;
        height: 100%;
        transition: all 0.3s ease;
        position: relative;
    }

    .rotacion-content.has-content {
        background: rgba(255, 255, 255, 0.35);
        border-color: rgba(43, 108, 79, 0.3);
    }

    .rotacion-content.empty {
        background: rgba(255, 255, 255, 0.15);
        border: 2px dashed rgba(27, 67, 50, 0.3);
    }

    .rotacion-content:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(27, 67, 50, 0.15);
        border-color: rgba(43, 108, 79, 0.5);
    }

    /* Estilos para imágenes de cultivos */
    .cultivo-imagen {
        text-align: center;
        margin-bottom: 1rem;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cultivo-img-real {
        max-width: 100%;
        max-height: 100px;
        border-radius: 12px;
        object-fit: cover;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .cultivo-img-real:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }

    .descanso-visual {
        color: #6c757d;
        text-align: center;
    }

    .descanso-visual i {
        margin-bottom: 0.5rem;
        display: block;
    }

    .descanso-text {
        font-size: 0.9rem;
        font-weight: 600;
    }

    .cultivo-placeholder {
        color: var(--andino-hoja);
        text-align: center;
    }

    .cultivo-placeholder i {
        margin-bottom: 0.5rem;
        display: block;
    }

    .placeholder-text {
        font-size: 0.8rem;
        color: var(--andino-oscuro);
    }

    .cultivo-info h5 {
        color: var(--andino-oscuro);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .cultivo-nombre {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--andino-oscuro);
        margin-bottom: 0.5rem;
    }

    /* Categorías de cultivos */
    .cultivo-meta {
        margin: 0.5rem 0;
    }

    .categoria, .carga-suelo {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
        margin-right: 0.3rem;
        margin-bottom: 0.2rem;
    }

    /* Colores por categoría */
    .categoria.tubérculo { background: #e3f2fd; color: #1976d2; }
    .categoria.leguminosa { background: #e8f5e8; color: #2e7d32; }
    .categoria.cereal { background: #fff3e0; color: #ef6c00; }
    .categoria.hortaliza { background: #fce4ec; color: #c2185b; }

    /* Colores por carga de suelo */
    .carga-suelo.alta { background: #ffebee; color: #c62828; }
    .carga-suelo.media { background: #fff3e0; color: #ef6c00; }
    .carga-suelo.baja { background: #e8f5e8; color: #2e7d32; }
    .carga-suelo.regenerativa { background: #e3f2fd; color: #1565c0; }

    .badge-descanso {
        background: linear-gradient(135deg, #6c757d, #495057);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }

    /* Badge de ejecución mejorado */
    .ejecucion-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(40, 167, 69, 0.9);
        color: white;
        border-radius: 20px;
        padding: 0.3rem 0.6rem;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        backdrop-filter: blur(10px);
    }

    .ejecucion-badge i {
        font-size: 0.8rem;
    }

    .fechas {
        display: block;
        margin-top: 0.5rem;
        font-style: italic;
    }

    .rotacion-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
    }

    .center-content {
        background: linear-gradient(135deg, var(--andino-oscuro), var(--andino-hoja));
        color: white;
        border-radius: 50%;
        width: 150px;
        height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        box-shadow: 0 10px 30px rgba(27, 67, 50, 0.3);
    }

    .rotacion-actions {
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .rotacion-item:hover .rotacion-actions {
        opacity: 1;
    }

    .empty-state {
        text-align: center;
        color: rgba(27, 67, 50, 0.5);
        padding: 2rem 1rem;
    }

    .empty-state i {
        margin-bottom: 1rem;
    }

    .info-card {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .stat-box {
        padding: 1rem;
    }

    .stat-box h4 {
        color: var(--andino-oscuro);
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .additional-year {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Posicionamiento específico de los items */
    .item-1 { grid-column: 1; grid-row: 1; }
    .item-2 { grid-column: 2; grid-row: 1; }
    .item-3 { grid-column: 2; grid-row: 2; }
    .item-4 { grid-column: 1; grid-row: 2; }

    /* Responsive */
    @media (max-width: 768px) {
        .rotacion-grid {
            grid-template-columns: 1fr;
            grid-template-rows: repeat(4, 1fr);
            gap: 20px;
            min-height: auto;
        }
        
        .rotacion-center {
            position: relative;
            top: auto;
            left: auto;
            transform: none;
            margin: 2rem auto;
        }
        
        .item-1, .item-2, .item-3, .item-4 {
            grid-column: 1;
        }
        
        .item-1 { grid-row: 1; }
        .item-2 { grid-row: 2; }
        .item-3 { grid-row: 3; }
        .item-4 { grid-row: 4; }
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Efectos de hover mejorados
        const rotacionItems = document.querySelectorAll('.rotacion-item');
        
        rotacionItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Tooltips para badges de ejecución
        const ejecucionBadges = document.querySelectorAll('.ejecucion-badge');
        ejecucionBadges.forEach(badge => {
            badge.setAttribute('title', 'Ejecución registrada');
        });

        // Efectos hover para imágenes
        const cultivoImagenes = document.querySelectorAll('.cultivo-img-real');
        cultivoImagenes.forEach(img => {
            img.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.08)';
            });
            
            img.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });
    });
</script>
@stop