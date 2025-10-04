@extends('adminlte::page')

@section('title', 'Detalle de Parcela')

@section('content_header')
    <h1><i class="fas fa-leaf"></i> Detalle de Parcela</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <p><strong><i class="fas fa-user"></i> Agricultor:</strong> {{ $parcela->agricultor->name ?? 'N/A' }}</p>
        <p><strong><i class="fas fa-signature"></i> Nombre:</strong> {{ $parcela->nombre }}</p>
        <p><strong><i class="fas fa-ruler-combined"></i> Superficie:</strong> {{ number_format($parcela->extension,2) }} m²</p>
        <p><strong><i class="fas fa-map-marker-alt"></i> Ubicación:</strong> {{ $parcela->ubicacion }}</p>
        <p><strong><i class="fas fa-mountain"></i> Tipo de Suelo:</strong> 
            <span class="badge badge-success">{{ $parcela->tipoSuelo }}</span>
        </p>
        <p><strong><i class="fas fa-tractor"></i> Uso del Suelo:</strong> 
            <span class="badge badge-info">{{ $parcela->usoSuelo }}</span>
        </p>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h5><i class="fas fa-map"></i> Mapa de la Parcela</h5>
    </div>
    <div class="card-body">
        <div id="map" style="height:400px;"></div>
    </div>
</div>

<div class="mt-3 text-right">
    <a href="{{ route('parcelas.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
@stop

@section('js')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
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
