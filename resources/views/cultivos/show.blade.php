@extends('adminlte::page')

@section('title', 'Detalle de Cultivo')

@section('content_header')
<h1><i class="fas fa-leaf"></i> Detalle de Cultivo</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="row">
            {{-- Imagen destacada --}}
            <div class="col-md-4 text-center">
                @if($cultivo->imagen && file_exists(public_path($cultivo->imagen)))
                    <img src="{{ asset($cultivo->imagen) }}" class="img-fluid rounded shadow-sm mb-3"
                        alt="{{ $cultivo->nombre }}" style="max-height: 250px; object-fit: cover;">
                @else
                    <img src="{{ asset('images/default-cultivo.png') }}" class="img-fluid rounded shadow-sm mb-3"
                        alt="Sin imagen" style="max-height: 250px; object-fit: cover;">
                @endif
                <h4 class="text-success font-weight-bold">{{ $cultivo->nombre }}</h4>
            </div>


            {{-- Información general --}}
            <div class="col-md-8">
                <p><strong><i class="fas fa-tags"></i> Categoría:</strong> {{ $cultivo->categoria }}</p>
                <p><strong><i class="fas fa-seedling"></i> Carga de Suelo:</strong>
                    <span class="badge 
                        @if($cultivo->cargaSuelo == 'alta') badge-danger 
                        @elseif($cultivo->cargaSuelo == 'media') badge-warning 
                        @elseif($cultivo->cargaSuelo == 'baja') badge-success 
                        @else badge-info @endif">
                        {{ ucfirst($cultivo->cargaSuelo) }}
                    </span>
                </p>
                <p><strong><i class="fas fa-clock"></i> Días de Cultivo:</strong> {{ $cultivo->diasCultivo }} días</p>
                <p><strong><i class="fas fa-calendar-plus"></i> Época de Siembra:</strong> {{ $cultivo->epocaSiembra }}
                </p>
                <p><strong><i class="fas fa-calendar-check"></i> Época de Cosecha:</strong> {{ $cultivo->epocaCosecha }}
                </p>
                @if($cultivo->variedad)
                    <p><strong><i class="fas fa-leaf"></i> Variedad:</strong> {{ $cultivo->variedad }}</p>
                @endif
            </div>
        </div>

        {{-- Sección de descripción y recomendaciones --}}
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h5><i class="fas fa-align-left"></i> Descripción</h5>
                <p>{{ $cultivo->descripcion ?? 'No disponible' }}</p>
            </div>
            <div class="col-md-6">
                <h5><i class="fas fa-lightbulb"></i> Recomendaciones</h5>
                <p>{{ $cultivo->recomendaciones ?? 'No disponible' }}</p>
            </div>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between">
        <a href="{{ route('cultivos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
        <div>
            <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <form action="{{ route('cultivos.destroy', $cultivo) }}" method="POST" class="d-inline"
                onsubmit="return confirm('¿Seguro que deseas eliminar este cultivo?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
@stop
