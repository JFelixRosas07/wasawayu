@extends('adminlte::page')

@section('title', 'Registrar Ejecución Real')

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1 class="mb-2">
        <i class="fas fa-tractor text-success me-2"></i>
        Registrar Ejecución Real
    </h1>
    <a href="{{ route('planes.show', $detalle->plan->id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Volver al Plan
    </a>
</div>
<p class="text-muted">
    Plan: <strong>{{ $detalle->plan->nombre }}</strong> | 
    Año {{ $detalle->anio }} | 
    {{ $detalle->es_descanso ? 'Descanso' : ($detalle->cultivo->nombre ?? 'Sin cultivo planificado') }}
</p>
@stop

@section('content')
<div class="container-fluid">
    <div class="row g-4">
       <!-- Panel izquierdo: Planificación -->
<div class="col-md-5">
    <div class="info-card p-3 shadow-sm" style="border-radius: 16px; background: rgba(255,255,255,0.25);">
        <h5 class="text-success"><i class="fas fa-calendar-alt me-2"></i>Planificación</h5>
        <ul class="list-group list-group-flush mb-3">

            <!-- Cultivo planificado -->
            <li class="list-group-item bg-transparent">
                <strong>Cultivo planificado:</strong> 
                @if($detalle->es_descanso)
                    <span class="badge bg-secondary">Descanso</span>
                @else
                    {{ $detalle->cultivo->nombre ?? 'No definido' }}
                @endif
            </li>

            <!-- Parcela asociada -->
            <li class="list-group-item bg-transparent">
                <strong>Parcela:</strong> 
                {{ $detalle->plan->parcela->nombre ?? 'No especificada' }}
            </li>

            <!-- Agricultor responsable -->
            <li class="list-group-item bg-transparent">
                <strong>Agricultor:</strong> 
                {{ $detalle->plan->parcela->agricultor->name ?? 'No asignado' }}
            </li>

            <!-- Tipo de suelo -->
            <li class="list-group-item bg-transparent">
                <strong>Tipo de suelo:</strong> 
                {{ $detalle->plan->parcela->tipoSuelo ?? 'No especificado' }}
            </li>

            <!-- Fechas de planificación -->
            <li class="list-group-item bg-transparent">
                <strong>Inicio:</strong> {{ optional($detalle->fecha_inicio)->format('d/m/Y') ?? 'No definido' }}
            </li>
            <li class="list-group-item bg-transparent">
                <strong>Fin:</strong> {{ optional($detalle->fecha_fin)->format('d/m/Y') ?? 'No definido' }}
            </li>

            <!-- Estado -->
            <li class="list-group-item bg-transparent">
                <strong>Estado:</strong> 
                <span class="badge bg-info text-dark">{{ ucfirst($detalle->estado ?? 'pendiente') }}</span>
            </li>
        </ul>

        <!-- Imagen del cultivo planificado -->
        @if(!$detalle->es_descanso && $detalle->cultivo && $detalle->cultivo->imagen)
            <div class="text-center mt-3">
                <img src="{{ asset($detalle->cultivo->imagen) }}" 
                     alt="{{ $detalle->cultivo->nombre }}" 
                     class="img-fluid rounded shadow-sm"
                     style="max-height: 215px; object-fit: cover; width: 100%; border-radius: 14px;">
                <p class="mt-3 text-muted small">
                    {{ $detalle->cultivo->nombre }} – {{ ucfirst($detalle->cultivo->categoria) }}
                </p>
            </div>
        @endif
    </div>
</div>


        <!-- Panel derecho: Ejecución real -->
        <div class="col-md-7">
            <div class="p-4 shadow-lg" style="border-radius: 20px; background: rgba(255,255,255,0.35);">
                <h5 class="text-success mb-3"><i class="fas fa-leaf me-2"></i>Datos de Ejecución Real</h5>

                <form action="{{ route('ejecuciones.store', $detalle->id) }}" method="POST" id="ejecucionForm">
                    @csrf

                    <!-- Cultivo real -->
                    @php
                        // Si el detalle tiene cultivo planificado, usarlo como valor inicial
                        $cultivoSeleccionado = old('cultivo_real_id', $detalle->cultivo_id);
                    @endphp

                    <div class="form-group mb-3">
                        <label for="cultivo_real_id"><strong>Cultivo Real *</strong></label>
                        <select name="cultivo_real_id" id="cultivo_real_id"
                                class="form-select {{ !$detalle->cultivo_id ? 'border-warning bg-light' : '' }}" required>
                            <option value="">-- Seleccione el cultivo real --</option>
                            @foreach($cultivos as $cultivo)
                                <option value="{{ $cultivo->id }}" 
                                    {{ $cultivoSeleccionado == $cultivo->id ? 'selected' : '' }}>
                                    {{ $cultivo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fechas -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label><strong>Fecha real de siembra *</strong></label>
                            <input type="date" name="fecha_siembra" class="form-control"
                                   value="{{ old('fecha_siembra', optional($detalle->fecha_inicio)->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label><strong>Fecha real de cosecha</strong></label>
                            <input type="date" name="fecha_cosecha" class="form-control"
                                   value="{{ old('fecha_cosecha', optional($detalle->fecha_fin)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <!-- Producción -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label><strong>Cant. sembrada</strong></label>
                            <input type="number" step="0.01" name="cantidad_sembrada" id="cantidad_sembrada"
                                   class="form-control" value="{{ old('cantidad_sembrada') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><strong>Cant. cosechada</strong></label>
                            <input type="number" step="0.01" name="cantidad_cosechada" id="cantidad_cosechada"
                                   class="form-control" value="{{ old('cantidad_cosechada') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><strong>Unidad</strong></label>
                            <select name="unidad_medida" id="unidad_medida" class="form-select">
                                @foreach(['cargas','arrobas','kg','qq','libras'] as $unidad)
                                    <option value="{{ $unidad }}" 
                                        {{ old('unidad_medida', 'cargas') == $unidad ? 'selected' : '' }}>
                                        {{ ucfirst($unidad) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Área y rendimientos -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label><strong>Área cultivada (ha)</strong></label>
                            <input type="number" step="0.01" name="area_cultivada" id="area_cultivada"
                                   class="form-control" value="{{ old('area_cultivada') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><strong>Rendimiento de Producción (%)</strong></label>
                            <input type="text" name="rendimiento_produccion" id="rendimiento_produccion"
                                   class="form-control bg-light" readonly placeholder="Automático">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label><strong>Rendimiento de Parcela (por ha)</strong></label>
                            <input type="text" name="rendimiento_parcela" id="rendimiento_parcela"
                                   class="form-control bg-light" readonly placeholder="Automático">
                        </div>
                    </div>

                    <!-- Resultado -->
                    <div class="form-group mb-3">
                        <label><strong>Resultado del ciclo</strong></label>
                        <div class="d-flex gap-3 mt-2">
                            <label class="text-success">
                                <input type="radio" name="fue_exitoso" value="si" 
                                       {{ old('fue_exitoso') == 'si' ? 'checked' : '' }}> 
                                <i class="fas fa-check-circle"></i> Exitoso
                            </label>
                            <label class="text-warning">
                                <input type="radio" name="fue_exitoso" value="parcial" 
                                       {{ old('fue_exitoso') == 'parcial' ? 'checked' : '' }}> 
                                <i class="fas fa-adjust"></i> Parcial
                            </label>
                            <label class="text-danger">
                                <input type="radio" name="fue_exitoso" value="no" 
                                       {{ old('fue_exitoso') == 'no' ? 'checked' : '' }}> 
                                <i class="fas fa-times-circle"></i> Fallido
                            </label>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="form-group mb-4">
                        <label><strong>Observaciones</strong></label>
                        <textarea name="observaciones" rows="3" class="form-control"
                                  placeholder="Condiciones climáticas, plagas, rendimiento, etc.">{{ old('observaciones') }}</textarea>
                    </div>

                    <!-- Botones -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Registrar Ejecución
                        </button>
                        <a href="{{ route('planes.show', $detalle->plan->id) }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const sembrada = document.getElementById('cantidad_sembrada');
    const cosechada = document.getElementById('cantidad_cosechada');
    const area = document.getElementById('area_cultivada');
    const rendimientoProd = document.getElementById('rendimiento_produccion');
    const rendimientoParc = document.getElementById('rendimiento_parcela');

    function calcularRendimientos() {
        const s = parseFloat(sembrada.value) || 0;
        const c = parseFloat(cosechada.value) || 0;
        const a = parseFloat(area.value) || 0;

        if (s > 0 && c > 0) {
            const prod = (c / s) * 100;
            rendimientoProd.value = `${prod.toFixed(2)} %`;
        } else {
            rendimientoProd.value = '';
        }

        if (c > 0 && a > 0) {
            const parc = c / a;
            rendimientoParc.value = `${parc.toFixed(2)} por ha`;
        } else {
            rendimientoParc.value = '';
        }
    }

    sembrada.addEventListener('input', calcularRendimientos);
    cosechada.addEventListener('input', calcularRendimientos);
    area.addEventListener('input', calcularRendimientos);
});
</script>
@stop
