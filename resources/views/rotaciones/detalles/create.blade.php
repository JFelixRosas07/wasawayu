@extends('adminlte::page')

@section('title', 'Agregar Detalle de Rotación')

@section('content_header')
    <h1>Agregar detalle al Plan: {{ $plan->nombre }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('planes.show', $plan->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al plan
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('detalles.store', $plan->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="anio">Año *</label>
                    <input type="number" name="anio" id="anio" 
                           class="form-control @error('anio') is-invalid @enderror" 
                           value="{{ old('anio') }}" 
                           min="1" max="{{ $plan->anios }}" required>
                    @error('anio')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">
                        Año dentro del plan (1 a {{ $plan->anios }})
                    </small>
                </div>

                <div class="form-group">
                    <label for="cultivo_id">Cultivo</label>
                    <select name="cultivo_id" id="cultivo_id" 
                            class="form-control @error('cultivo_id') is-invalid @enderror">
                        <option value="">-- Seleccionar cultivo --</option>
                        @foreach($cultivos as $cultivo)
                            <option value="{{ $cultivo->id }}"
                                {{ old('cultivo_id') == $cultivo->id ? 'selected' : '' }}>
                                {{ $cultivo->nombre }} ({{ $cultivo->categoria }})
                            </option>
                        @endforeach
                    </select>
                    @error('cultivo_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="es_descanso" id="es_descanso" value="1"
                           class="form-check-input @error('es_descanso') is-invalid @enderror"
                           {{ old('es_descanso') ? 'checked' : '' }}>
                    <label for="es_descanso" class="form-check-label">Este año es Descanso</label>
                    @error('es_descanso')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">
                        Si se marca, el cultivo seleccionado será ignorado
                    </small>
                </div>

                <div class="form-group">
                    <label for="fecha_inicio">Fecha de inicio planificada</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" 
                           class="form-control @error('fecha_inicio') is-invalid @enderror"
                           value="{{ old('fecha_inicio') }}">
                    @error('fecha_inicio')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="fecha_fin">Fecha de fin planificada</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" 
                           class="form-control @error('fecha_fin') is-invalid @enderror"
                           value="{{ old('fecha_fin') }}">
                    @error('fecha_fin')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Detalle
                </button>
                <a href="{{ route('planes.show', $plan->id) }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    // JavaScript para deshabilitar cultivo cuando es descanso
    document.addEventListener('DOMContentLoaded', function() {
        const cultivoSelect = document.getElementById('cultivo_id');
        const descansoCheckbox = document.getElementById('es_descanso');
        
        function toggleCultivo() {
            if (descansoCheckbox.checked) {
                cultivoSelect.disabled = true;
                cultivoSelect.value = '';
            } else {
                cultivoSelect.disabled = false;
            }
        }
        
        // Ejecutar al cargar y cuando cambie el checkbox
        toggleCultivo();
        descansoCheckbox.addEventListener('change', toggleCultivo);
    });
</script>
@stop