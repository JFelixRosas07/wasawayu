@extends('adminlte::page')

@section('title', 'Nuevo Plan de Rotación')

@section('content_header')
    <h1>Crear Plan de Rotación</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('planes.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="nombre">Nombre del plan</label>
                    <input type="text" name="nombre" id="nombre"
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre') }}" required>
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
                                {{ old('parcela_id') == $parcela->id ? 'selected' : '' }}>
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
                           value="{{ old('anios', 4) }}" required>
                    @error('anios')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <a href="{{ route('planes.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@stop
