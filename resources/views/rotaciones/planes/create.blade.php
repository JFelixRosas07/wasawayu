@extends('adminlte::page')

@section('title', 'Nuevo Plan de Rotación')

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Crear Plan de Rotación</h1>
@stop

@section('content')
@php
    $parcelaId = request('parcela_id');
@endphp

<div class="card">
    <div class="card-body">
        
        {{-- Mensaje informativo --}}
        <div class="alert alert-success">
            <i class="fas fa-info-circle"></i>
            Cada plan de rotación tiene una <b>duración fija de 4 años</b>. 
            El sistema determinará automáticamente el <b>año de inicio</b> según el último plan registrado de la parcela.
        </div>

        {{-- Mostrar información de la parcela si viene desde el dashboard --}}
        @if($parcelaId)
            @php
                $parcelaSel = $parcelas->firstWhere('id', $parcelaId);
            @endphp
            @if($parcelaSel)
                <div class="card border-success mb-3">
                    <div class="card-body py-2">
                        <strong>Parcela seleccionada:</strong> {{ $parcelaSel->nombre }} <br>
                        <small>Agricultor: {{ $parcelaSel->agricultor->name ?? 'N/A' }}</small>
                    </div>
                </div>

                {{-- Mostrar bloqueo si existe --}}
                @if(isset($bloqueosPorParcela[$parcelaId]))
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Advertencia:</strong> {{ $bloqueosPorParcela[$parcelaId] }}
                        <br>
                        <small>Debe finalizar el plan actual o esperar a que termine su ciclo antes de crear uno nuevo.</small>
                    </div>
                @endif
            @endif
        @endif

        {{-- Mostrar errores de validación --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <strong>Error al crear el plan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario principal --}}
        <form action="{{ route('planes.store') }}" method="POST">
            @csrf

            {{-- Campo oculto para mantener el parcela_id en la redirección --}}
            <input type="hidden" name="redirect_parcela_id" value="{{ $parcelaId }}">

            {{-- Nombre del plan --}}
            <div class="form-group">
                <label for="nombre">
                    <i class="fas fa-leaf"></i> Nombre del plan <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       name="nombre" 
                       id="nombre"
                       class="form-control @error('nombre') is-invalid @enderror"
                       placeholder="Ejemplo: P-01"
                       value="{{ old('nombre') }}" 
                       required 
                       autofocus>
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">
                    Usa un código corto para identificar el plan (ejemplo: <b>P-01</b>).
                </small>
            </div>

            {{-- Campo de Parcela (dinámico según contexto) --}}
            @if(!$parcelaId)
                {{-- Mostrar selector si NO viene filtrado --}}
                <div class="form-group">
                    <label for="parcela_id">
                        <i class="fas fa-map-marker-alt"></i> Parcela <span class="text-danger">*</span>
                    </label>
                    <select name="parcela_id" 
                            id="parcela_id"
                            class="form-control @error('parcela_id') is-invalid @enderror" 
                            required>
                        <option value="">-- Seleccione una parcela --</option>
                        @foreach($parcelas as $parcela)
                            @php
                                $bloqueada = isset($bloqueosPorParcela[$parcela->id]);
                                $inicioEstimado = $inicioEstimadoPorParcela[$parcela->id] ?? now()->year;
                            @endphp
                            <option value="{{ $parcela->id }}"
                                    data-inicio="{{ $inicioEstimado }}"
                                    {{ old('parcela_id') == $parcela->id ? 'selected' : '' }}
                                    {{ $bloqueada ? 'disabled' : '' }}>
                                {{ $parcela->nombre }} (Agricultor: {{ $parcela->agricultor->name ?? 'N/A' }})
                                @if($bloqueada)
                                    - ⚠️ Plan activo hasta {{ $inicioEstimado - 1 }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('parcela_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">
                        Las parcelas con planes activos están deshabilitadas.
                    </small>
                </div>
            @else
                {{-- Campo oculto + mostrar info si viene filtrado --}}
                <input type="hidden" name="parcela_id" value="{{ $parcelaId }}">
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Parcela</label>
                    <input type="text" 
                           class="form-control" 
                           value="{{ $parcelaSel->nombre ?? 'N/A' }} (Agricultor: {{ $parcelaSel->agricultor->name ?? 'N/A' }})" 
                           readonly>
                    <small class="form-text text-muted">
                        Parcela seleccionada desde el dashboard. 
                        <a href="{{ route('planes.create') }}">Cambiar parcela</a>
                    </small>
                </div>
            @endif

            {{-- Ciclo estimado --}}
            @php
                $anioInicio = $anioInicioEstimado ?? now()->year;
                $anioFin = $anioInicio + 3; // 4 años - 1
            @endphp
            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Ciclo estimado</label>
                <input type="text" 
                       id="ciclo" 
                       class="form-control" 
                       value="Ciclo {{ $anioInicio }}–{{ $anioFin }}" 
                       readonly>
                <small class="form-text text-muted">
                    El ciclo se ajusta automáticamente según el último plan de la parcela seleccionada.
                </small>
            </div>

            {{-- Duración (fija) --}}
            <div class="form-group">
                <label><i class="fas fa-hourglass-half"></i> Duración</label>
                <input type="text" 
                       class="form-control" 
                       value="4 años (fijo)" 
                       readonly>
            </div>

            {{-- Botones de acción --}}
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="{{ route('planes.index', $parcelaId ? ['parcela_id' => $parcelaId] : []) }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectParcela = document.getElementById('parcela_id');
    const inputCiclo = document.getElementById('ciclo');

    // Solo ejecutar si existe el selector (cuando NO viene filtrado)
    if (selectParcela && inputCiclo) {
        selectParcela.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const inicio = parseInt(selectedOption.getAttribute('data-inicio')) || new Date().getFullYear();
            const fin = inicio + 3; // 4 años - 1
            
            inputCiclo.value = `Ciclo ${inicio}–${fin}`;
        });

        // Ejecutar al cargar si hay una parcela pre-seleccionada (old value)
        if (selectParcela.value) {
            selectParcela.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@stop