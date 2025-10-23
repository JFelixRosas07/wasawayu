@extends('adminlte::page')

@section('title', 'Editar Parcela')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit"></i> Editar Parcela</h1>
        <a href="{{ route('parcelas.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <form action="{{ route('parcelas.update', $parcela) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label><i class="fas fa-user"></i> Agricultor</label>
                <select name="agricultor_id" class="form-control" required>
                    @foreach($agricultores as $id => $nombre)
                        <option value="{{ $id }}" @if($id == $parcela->agricultor_id) selected @endif>
                            {{ $nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-signature"></i> Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre', $parcela->nombre) }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-ruler-combined"></i> Superficie (m²)</label>
                <input type="number" step="0.01" name="extension" value="{{ old('extension', $parcela->extension) }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Ubicación</label>
                <input type="text" name="ubicacion" value="{{ old('ubicacion', $parcela->ubicacion) }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-mountain"></i> Tipo de Suelo</label>
                <select name="tipoSuelo" class="form-control" required>
                    <option value="Arenoso" @if($parcela->tipoSuelo=="Arenoso") selected @endif>Arenoso</option>
                    <option value="Arcilloso" @if($parcela->tipoSuelo=="Arcilloso") selected @endif>Arcilloso</option>
                    <option value="Franco" @if($parcela->tipoSuelo=="Franco") selected @endif>Franco</option>
                    <option value="Pedregoso" @if($parcela->tipoSuelo=="Pedregoso") selected @endif>Pedregoso</option>
                    <option value="Limoso" @if($parcela->tipoSuelo=="Limoso") selected @endif>Limoso</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-tractor"></i> Uso del Suelo</label>
                <select name="usoSuelo" class="form-control" required>
                    <option value="Agrícola" @if($parcela->usoSuelo=="Agrícola") selected @endif>Agrícola</option>
                    <option value="Ganadero" @if($parcela->usoSuelo=="Ganadero") selected @endif>Ganadero</option>
                    <option value="Forestal" @if($parcela->usoSuelo=="Forestal") selected @endif>Forestal</option>
                    <option value="Mixto" @if($parcela->usoSuelo=="Mixto") selected @endif>Mixto</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-draw-polygon"></i> Editar polígono en el mapa</label>
                <div id="map" style="height:400px;"></div>
                <input type="hidden" name="poligono" id="poligono" value='@json($parcela->poligono)'>
            </div>

            <div class="text-right mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Parcela
                </button>
                <a href="{{ route('parcelas.show', $parcela) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Ver Detalles
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
<style>
    .card {
        border: none;
        border-radius: 10px;
    }
    .btn {
        border-radius: 6px;
        margin-left: 5px;
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
    var map = L.map('map').setView([-17.582086030305437, -65.70528192684172], 17);
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/' +
        'World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri, Earthstar Geographics, Maxar',
        maxZoom: 17
    }).addTo(map);

    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);

    var drawControl = new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: { polygon: true, marker: false, circle: false, polyline: false, rectangle: false }
    });
    map.addControl(drawControl);

    var poligono = {!! json_encode($parcela->poligono) !!};
    if (poligono) {
        var layer = L.geoJSON(poligono).getLayers()[0];
        drawnItems.addLayer(layer);
        map.fitBounds(layer.getBounds());
    }

    map.on(L.Draw.Event.CREATED, function (e) {
        drawnItems.clearLayers();
        var layer = e.layer;
        drawnItems.addLayer(layer);
        document.getElementById('poligono').value = JSON.stringify(layer.toGeoJSON());
    });

    map.on('draw:edited', function () {
        drawnItems.eachLayer(function (layer) {
            document.getElementById('poligono').value = JSON.stringify(layer.toGeoJSON());
        });
    });
</script>
@stop