@extends('adminlte::page')

@section('title', 'Editar Plan de Rotación')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Plan: {{ $plan->nombre }}</h1>
@stop

@section('content')
@php
    $parcelaId = request('parcela_id');
@endphp

<div class="card">
    <div class="card-header">
        <a href="{{ route('planes.show', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al plan
        </a>
    </div>
    <div class="card-body">
        {{-- Información del ciclo actual --}}
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>Información del plan:</strong> 
            Ciclo actual: <strong>{{ $plan->ciclo }}</strong> | 
            Duración: <strong>4 años (fijo)</strong>
        </div>

        <form action="{{ route('planes.update', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Nombre del plan --}}
            <div class="form-group">
                <label for="nombre">
                    <i class="fas fa-leaf"></i> Nombre del plan <span class="text-danger">*</span>
                </label>
                <input type="text" name="nombre" id="nombre" 
                       class="form-control @error('nombre') is-invalid @enderror" 
                       value="{{ old('nombre', $plan->nombre) }}" 
                       placeholder="Ejemplo: P-01"
                       required>
                @error('nombre')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">
                    Código corto para identificar el plan.
                </small>
            </div>

            {{-- Parcela --}}
            <div class="form-group">
                <label for="parcela_id">
                    <i class="fas fa-map-marker-alt"></i> Parcela <span class="text-danger">*</span>
                </label>
                <select name="parcela_id" id="parcela_id" 
                        class="form-control @error('parcela_id') is-invalid @enderror" required>
                    <option value="">-- Seleccione una parcela --</option>
                    @foreach($parcelas as $parcela)
                        <option value="{{ $parcela->id }}"
                            {{ old('parcela_id', $plan->parcela_id) == $parcela->id ? 'selected' : '' }}>
                            {{ $parcela->nombre }} (Agricultor: {{ $parcela->agricultor->name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('parcela_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                @if($plan->detalles()->exists())
                    <small class="form-text text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No se recomienda cambiar la parcela si ya tiene detalles registrados.
                    </small>
                @endif
            </div>

            {{-- Año de inicio --}}
            <div class="form-group">
                <label for="anio_inicio">
                    <i class="fas fa-calendar-alt"></i> Año de inicio <span class="text-danger">*</span>
                </label>
                <input type="number" name="anio_inicio" id="anio_inicio" 
                       class="form-control @error('anio_inicio') is-invalid @enderror"
                       value="{{ old('anio_inicio', $plan->anio_inicio) }}" 
                       min="2020" max="2030"
                       required>
                @error('anio_inicio')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
                <small class="form-text text-muted">
                    Año en que inicia el ciclo del plan. El ciclo terminará en {{ ($plan->anio_inicio ?? now()->year) + 3 }}.
                </small>
            </div>

            {{-- Información de ciclo calculado --}}
            <div class="form-group">
                <label><i class="fas fa-sync-alt"></i> Ciclo completo</label>
                <input type="text" class="form-control" 
                       value="Ciclo {{ $plan->anio_inicio }}–{{ $plan->anio_fin }}" 
                       readonly>
                <small class="form-text text-muted">
                    Duración fija de 4 años.
                </small>
            </div>

            {{-- Estado --}}
            <div class="form-group">
                <label><i class="fas fa-tasks"></i> Estado actual</label>
                <div>
                    <span class="badge {{ $plan->badge_estado }} badge-pill">
                        {{ $plan->estado_texto }}
                    </span>
                </div>
                <small class="form-text text-muted">
                    El estado se calcula automáticamente según las fechas.
                </small>
            </div>

            {{-- Botones --}}
            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Plan
                </button>
                <a href="{{ route('planes.index', $parcelaId ? ['parcela_id' => $parcelaId] : []) }}" 
                   class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputAnioInicio = document.getElementById('anio_inicio');
    const inputCiclo = document.querySelector('input[readonly]');

    if (inputAnioInicio && inputCiclo) {
        inputAnioInicio.addEventListener('change', function() {
            const anioInicio = parseInt(this.value) || new Date().getFullYear();
            const anioFin = anioInicio + 3;
            inputCiclo.value = `Ciclo ${anioInicio}–${anioFin}`;
        });
    }
});
</script>
@stop