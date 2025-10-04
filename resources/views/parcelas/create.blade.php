@extends('adminlte::page')

@section('title', 'Nueva Parcela')

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Nueva Parcela</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        {{-- Mensajes de error --}}
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <form action="{{ route('parcelas.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label><i class="fas fa-user"></i> Agricultor</label>
                <select name="agricultor_id" class="form-control" required>
                    <option value="">Seleccione...</option>
                    @foreach($agricultores as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-signature"></i> Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-ruler-combined"></i> Superficie (m²)</label>
                <input type="number" step="0.01" name="extension" class="form-control" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-mountain"></i> Tipo de Suelo</label>
                <select name="tipoSuelo" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <option value="Arenoso">Arenoso</option>
                    <option value="Arcilloso">Arcilloso</option>
                    <option value="Franco">Franco</option>
                    <option value="Pedregoso">Pedregoso</option>
                    <option value="Limoso">Limoso</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-tractor"></i> Uso del Suelo</label>
                <select name="usoSuelo" class="form-control" required>
                    <option value="">Seleccione...</option>
                    <option value="Agrícola">Agrícola</option>
                    <option value="Ganadero">Ganadero</option>
                    <option value="Forestal">Forestal</option>
                    <option value="Mixto">Mixto</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-draw-polygon"></i> Dibuje la parcela en el mapa (Wasawayu)</label>
                <div id="map" style="height:500px; border:1px solid #ccc;"></div>
                <input type="hidden" name="poligono" id="poligono">
            </div>

            <div class="text-right">
                <button class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
                <a href="{{ route('parcelas.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
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

        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            var layer = e.layer;
            drawnItems.addLayer(layer);
            document.getElementById('poligono').value = JSON.stringify(layer.toGeoJSON());
        });
    </script>
@stop
