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
                <label><i class="fas fa-signature"></i> Código Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-ruler-combined"></i> Superficie (ha)</label>
                <input type="number" step="0.0001" name="extension" id="extension" class="form-control" required readonly>
                <small class="text-muted">Se calculará automáticamente al dibujar el polígono.</small>
            </div>

            <div class="form-group">
                <label><i class="fas fa-map-marker-alt"></i> Descripción de la Ubicación</label>
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
                <label><i class="fas fa-draw-polygon"></i> Dibuje la ubicación de la parcela en el mapa</label>
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

            // Coordenadas iniciales (Wasa Wayu)
            const initialCoords = [-17.5837, -65.7040];
            const map = L.map('map').setView(initialCoords, 15);

            // Capas base
            const googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
            }).addTo(map);

            const osmMap = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            });

            // Control de capas
            L.control.layers({ 'Satélite': googleSat, 'Mapa base': osmMap }).addTo(map);

            // Grupo de polígonos
            const drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            const parcelasLayer = L.featureGroup().addTo(map);

            // Mostrar parcelas existentes
            @foreach($parcelas ?? [] as $parcela)
                @if(!empty($parcela->poligono))
                    try {
                        const poligonoData = {!! json_encode($parcela->poligono) !!};
                        if (poligonoData && poligonoData.geometry) {
                            const layer = L.geoJSON(poligonoData, {
                                style: {
                                    fillColor: coloresSuelo['{{ $parcela->tipoSuelo }}'] || '#3498DB',
                                    color: '#2C3E50',
                                    weight: 2,
                                    fillOpacity: 0.6
                                }
                            }).bindPopup(`
                                <strong>{{ $parcela->nombre }}</strong><br>
                                {{ $parcela->agricultor->name ?? 'Sin agricultor' }}<br>
                                <small>{{ $parcela->tipoSuelo }} - {{ $parcela->usoSuelo }}</small>
                            `);
                            layer.addTo(parcelasLayer);
                        }
                    } catch (error) {
                        console.error('Error procesando parcela {{ $parcela->id }}:', error);
                    }
                @endif
            @endforeach

            if (parcelasLayer.getLayers().length > 0) {
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

                // Calcular área y guardar en hectáreas (sin ceros innecesarios)
                const area = turf.area(geojson); // m²
                const areaHa = parseFloat((area / 10000).toFixed(2)); // ha limpio
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
