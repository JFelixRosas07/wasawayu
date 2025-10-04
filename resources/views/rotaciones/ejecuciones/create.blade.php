@extends('adminlte::page')

@section('title', 'Registrar Ejecución de Rotación')

@section('content_header')
    <h1>Registrar Ejecución Real</h1>
    <p class="text-muted">
        Plan: <strong>{{ $detalle->plan->nombre }}</strong> | 
        Año {{ $detalle->anio }} | 
        {{ $detalle->es_descanso ? 'Descanso' : ($detalle->cultivo->nombre ?? 'Sin cultivo') }}
    </p>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('planes.show', $detalle->plan->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al plan
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Planificado:</h5>
                    <ul class="list-group">
                        <li class="list-group-item">
                            <strong>Cultivo/Descanso:</strong> 
                            @if($detalle->es_descanso)
                                <span class="badge badge-secondary">Descanso</span>
                            @else
                                {{ $detalle->cultivo->nombre ?? 'No asignado' }}
                            @endif
                        </li>
                        <li class="list-group-item">
                            <strong>Fecha inicio plan:</strong> 
                            {{ $detalle->fecha_inicio ? $detalle->fecha_inicio->format('d/m/Y') : 'No definida' }}
                        </li>
                        <li class="list-group-item">
                            <strong>Fecha fin plan:</strong> 
                            {{ $detalle->fecha_fin ? $detalle->fecha_fin->format('d/m/Y') : 'No definida' }}
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Información</h6>
                        <small>
                            Registra aquí lo que realmente se sembró/cosechó en este período.
                            El sistema comparará automáticamente con lo planificado.
                        </small>
                    </div>
                </div>
            </div>

            <form action="{{ route('ejecuciones.store', $detalle->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="fecha_siembra">Fecha real de siembra *</label>
                    <input type="date" name="fecha_siembra" id="fecha_siembra" 
                           class="form-control @error('fecha_siembra') is-invalid @enderror"
                           value="{{ old('fecha_siembra') }}" required>
                    @error('fecha_siembra')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="fecha_cosecha">Fecha real de cosecha (opcional)</label>
                    <input type="date" name="fecha_cosecha" id="fecha_cosecha" 
                           class="form-control @error('fecha_cosecha') is-invalid @enderror"
                           value="{{ old('fecha_cosecha') }}">
                    @error('fecha_cosecha')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">
                        Si la cosecha aún no ha terminado, deja este campo vacío.
                    </small>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" 
                              class="form-control @error('observaciones') is-invalid @enderror"
                              rows="3" placeholder="Clima, rendimiento, problemas, etc.">{{ old('observaciones') }}</textarea>
                    @error('observaciones')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle"></i> Registrar Ejecución
                </button>
                <a href="{{ route('planes.show', $detalle->plan->id) }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Validación básica de fechas
        const fechaSiembra = document.getElementById('fecha_siembra');
        const fechaCosecha = document.getElementById('fecha_cosecha');
        
        if (fechaCosecha.value && fechaSiembra.value) {
            if (fechaCosecha.value < fechaSiembra.value) {
                alert('⚠️ La fecha de cosecha no puede ser anterior a la fecha de siembra.');
            }
        }
    });
</script>
@stop