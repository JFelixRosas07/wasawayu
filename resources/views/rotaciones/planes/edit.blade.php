@extends('adminlte::page')

@section('title', 'Editar Plan de Rotación')

@section('content_header')
    <h1>Editar Plan: {{ $plan->nombre }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('planes.show', $plan->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al plan
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('planes.update', $plan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="nombre">Nombre del plan</label>
                    <input type="text" name="nombre" id="nombre" 
                           class="form-control @error('nombre') is-invalid @enderror" 
                           value="{{ old('nombre', $plan->nombre) }}" required>
                    @error('nombre')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="parcela_id">Parcela</label>
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
                </div>

                <div class="form-group">
                    <label for="anios">Número de años</label>
                    <input type="number" name="anios" id="anios" min="1" max="10"
                           class="form-control @error('anios') is-invalid @enderror"
                           value="{{ old('anios', $plan->anios) }}" required>
                    @error('anios')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Plan
                </button>
                <a href="{{ route('planes.show', $plan->id) }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@stop