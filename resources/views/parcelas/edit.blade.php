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
                <label><i class="fas fa-signature"></i> Código Nombre</label>
                <input type="text" name="nombre" value="{{ old('nombre', $parcela->nombre) }}" class="form-control" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-ruler-combined"></i> Superficie (ha)</label>
                <input type="number" step="0.0001" name="extension" id="extension" value="{{ old('extension', $parcela->extension) }}" class="form-control" required readonly>
                <small class="text-muted">Se calculará automáticamente al editar o dibujar el polígono.</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Descripción de la Ubicación</label>
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
                <div id="map" style="height:420px; border:1px solid #ccc;"></div>
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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const coloresSuelo = {
            'Arenoso': '#F9E79F',
            'Arcilloso': '#A569BD',
            'Franco': '#58D68D',
            'Pedregoso': '#7F8C8D',
            'Limoso': '#85C1E9'
        };

        const map = L.map('map').setView([-17.5837, -65.7040], 15);

        // Capas base
        const googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        }).addTo(map);

        const osmMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        });

        L.control.layers({ 'Satélite': googleSat, 'Mapa base': osmMap }).addTo(map);

        // Capas de parcelas existentes
        const parcelasLayer = L.featureGroup().addTo(map);

        @foreach($parcelas ?? [] as $p)
            @if(!empty($p->poligono))
                try {
                    const poligonoData = {!! json_encode($p->poligono) !!};
                    if (poligonoData && poligonoData.geometry) {
                        const layer = L.geoJSON(poligonoData, {
                            style: {
                                fillColor: coloresSuelo['{{ $p->tipoSuelo }}'] || '#3498DB',
                                color: '#2C3E50',
                                weight: 1.5,
                                fillOpacity: 0.5
                            }
                        }).bindPopup(`<strong>{{ $p->nombre }}</strong><br>
                            {{ $p->agricultor->name ?? 'Sin agricultor' }}<br>
                            <small>{{ $p->tipoSuelo }} - {{ $p->usoSuelo }}</small>`);
                        layer.addTo(parcelasLayer);
                    }
                } catch (error) {
                    console.error('Error en parcela {{ $p->id }}:', error);
                }
            @endif
        @endforeach

        // Grupo para edición
        const drawnItems = new L.FeatureGroup();
        map.addLayer(drawnItems);

        // Cargar polígono actual
        const poligono = {!! json_encode($parcela->poligono) !!};
        if (poligono) {
            const layer = L.geoJSON(poligono).getLayers()[0];
            drawnItems.addLayer(layer);
            map.fitBounds(layer.getBounds().pad(0.1));

            // Calcular área inicial en hectáreas
            const area = turf.area(layer.toGeoJSON());
            const areaHa = parseFloat((area / 10000).toFixed(2));
            document.getElementById('extension').value = areaHa;
            layer.bindPopup(`<b>Área:</b> ${area.toFixed(2)} m² (${areaHa} ha)`);
        } else if (parcelasLayer.getLayers().length > 0) {
            map.fitBounds(parcelasLayer.getBounds().pad(0.1));
        }

        // Control de dibujo
        const drawControl = new L.Control.Draw({
            draw: {
                polygon: true,
                polyline: false,
                rectangle: false,
                circle: false,
                marker: false,
                circlemarker: false
            },
            edit: { featureGroup: drawnItems }
        });
        map.addControl(drawControl);

        // Crear nuevo polígono
        map.on(L.Draw.Event.CREATED, function (e) {
            drawnItems.clearLayers();
            const layer = e.layer;
            drawnItems.addLayer(layer);

            const geojson = layer.toGeoJSON();
            document.getElementById('poligono').value = JSON.stringify(geojson);

            const area = turf.area(geojson);
            const areaHa = parseFloat((area / 10000).toFixed(2));
            document.getElementById('extension').value = areaHa;
            layer.bindPopup(`<b>Área:</b> ${area.toFixed(2)} m² (${areaHa} ha)`).openPopup();
        });

        // Editar polígono existente
        map.on(L.Draw.Event.EDITED, function () {
            const layers = drawnItems.getLayers();
            if (layers.length > 0) {
                const geojson = layers[0].toGeoJSON();
                document.getElementById('poligono').value = JSON.stringify(geojson);

                const area = turf.area(geojson);
                const areaHa = parseFloat((area / 10000).toFixed(2));
                document.getElementById('extension').value = areaHa;
                layers[0].bindPopup(`<b>Área:</b> ${area.toFixed(2)} m² (${areaHa} ha)`).openPopup();
            }
        });
    });
</script>
@stop
