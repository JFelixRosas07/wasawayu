@extends('adminlte::page')

@section('title', 'Detalle de Parcela')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-leaf"></i> Detalle de Parcela</h1>
        <a href="{{ route('parcelas.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información General</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong><i class="fas fa-user text-success"></i> Agricultor:</strong> 
                    {{ $parcela->agricultor->name ?? 'No asignado' }}
                </p>
                <p><strong><i class="fas fa-signature text-primary"></i> Nombre:</strong> 
                    {{ $parcela->nombre }}
                </p>
                <p><strong><i class="fas fa-ruler-combined text-warning"></i> Superficie:</strong> 
                    {{ number_format($parcela->extension, 2) }} m²
                </p>
            </div>
            <div class="col-md-6">
                <p><strong><i class="fas fa-map-marker-alt text-danger"></i> Ubicación:</strong> 
                    {{ $parcela->ubicacion }}
                </p>
                <p><strong><i class="fas fa-mountain text-info"></i> Tipo de Suelo:</strong> 
                    <span class="badge badge-success">{{ $parcela->tipoSuelo }}</span>
                </p>
                <p><strong><i class="fas fa-tractor text-secondary"></i> Uso del Suelo:</strong> 
                    <span class="badge badge-info">{{ $parcela->usoSuelo }}</span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-map"></i> Mapa de la Parcela</h5>
    </div>
    <div class="card-body">
        <div id="map" style="height:400px;"></div>
    </div>
</div>

{{-- Botones de Acción --}}
<div class="mt-4 d-flex justify-content-between">
    <div>
        {{-- Solo admin y técnico pueden editar --}}
        @if(auth()->user()->hasAnyRole(['Administrador', 'TecnicoAgronomo']))
            <a href="{{ route('parcelas.edit', $parcela) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar Parcela
            </a>
        @endif
    </div>
    <div>
        {{-- Solo se mantiene el botón de Mapa General --}}
        <a href="{{ route('parcelas.mapa-general') }}" class="btn btn-info">
            <i class="fas fa-map-marked-alt"></i> Ver Mapa General
        </a>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
    .card {
        border: none;
        border-radius: 10px;
    }
    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }
    .btn {
        border-radius: 6px;
        margin-left: 5px;
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // MAPA ORIGINAL - SIN MODIFICACIONES
    var map = L.map('map').setView([-17.582086030305437, -65.70528192684172], 17);
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/' +
        'World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri, Earthstar Geographics, Maxar',
        maxZoom: 17
    }).addTo(map);

    var poligono = {!! json_encode($parcela->poligono) !!};
    if (poligono) {
        var layer = L.geoJSON(poligono).getLayers()[0];
        layer.addTo(map);
        map.fitBounds(layer.getBounds());
    }
</script>
@stop