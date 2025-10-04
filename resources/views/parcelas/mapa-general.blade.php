@extends('adminlte::page')

@section('title', 'Mapa General de Parcelas')

@section('content_header')
    <h1><i class="fas fa-map-marked-alt"></i> Mapa General de Parcelas</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-layer-group"></i> Visualización de Todas las Parcelas Registradas</h5>
            <a href="{{ route('parcelas.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- Estadísticas Rápidas -->
        <div class="row m-3">
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-map"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Parcelas</span>
                        <span class="info-box-number">{{ $parcelas->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Agricultores</span>
                        <span class="info-box-number">{{ $parcelas->unique('agricultor_id')->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-warning">
                    <span class="info-box-icon"><i class="fas fa-ruler-combined"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Superficie Total</span>
                        <span class="info-box-number">{{ number_format($parcelas->sum('extension'), 2) }} m²</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-primary">
                    <span class="info-box-icon"><i class="fas fa-tractor"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Uso Agrícola</span>
                        <span class="info-box-number">{{ $parcelas->where('usoSuelo', 'Agrícola')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa -->
        <div id="map" style="height: 600px; width: 100%;"></div>

        <!-- Leyenda -->
        <div class="p-3 border-top">
            <h6><i class="fas fa-key"></i> Leyenda de Tipos de Suelo:</h6>
            <div class="d-flex flex-wrap gap-3">
                <span class="badge badge-pill" style="background-color: #F9E79F; color: #000;"><i class="fas fa-square"></i> Arenoso</span>
                <span class="badge badge-pill" style="background-color: #A569BD; color: #fff;"><i class="fas fa-square"></i> Arcilloso</span>
                <span class="badge badge-pill" style="background-color: #58D68D; color: #000;"><i class="fas fa-square"></i> Franco</span>
                <span class="badge badge-pill" style="background-color: #7F8C8D; color: #fff;"><i class="fas fa-square"></i> Pedregoso</span>
                <span class="badge badge-pill" style="background-color: #85C1E9; color: #000;"><i class="fas fa-square"></i> Limoso</span>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css"/>
@stop

@section('js')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
<script>
    // Colores para tipos de suelo
    const coloresSuelo = {
        'Arenoso': '#F9E79F',
        'Arcilloso': '#A569BD',
        'Franco': '#58D68D',
        'Pedregoso': '#7F8C8D',
        'Limoso': '#85C1E9'
    };

    // Inicializar mapa
    var map = L.map('map').setView([-17.582086030305437, -65.70528192684172], 17);

    // Capa base (Satélite)
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/' +
        'World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri, Earthstar Geographics, Maxar',
        maxZoom: 17
    }).addTo(map);

    // Grupo para los polígonos de parcelas
    var parcelasLayer = L.layerGroup().addTo(map);

    // Procesar cada parcela
    @foreach($parcelas as $parcela)
        @if(!empty($parcela->poligono))
            try {
                var poligonoData = {!! json_encode($parcela->poligono) !!};
                
                if (poligonoData && poligonoData.geometry) {
                    var layer = L.geoJSON(poligonoData, {
                        style: function(feature) {
                            return {
                                fillColor: coloresSuelo['{{ $parcela->tipoSuelo }}'] || '#3498DB',
                                color: '#2C3E50',
                                weight: 2,
                                opacity: 0.8,
                                fillOpacity: 0.6
                            };
                        }
                    });

                    // Agregar popup informativo
                    layer.bindPopup(`
                        <div class="parcela-popup">
                            <h6><strong>{{ $parcela->nombre }}</strong></h6>
                            <hr class="my-2">
                            <p class="mb-1"><strong><i class="fas fa-user"></i> Agricultor:</strong> {{ $parcela->agricultor->name ?? 'No asignado' }}</p>
                            <p class="mb-1"><strong><i class="fas fa-ruler-combined"></i> Superficie:</strong> {{ number_format($parcela->extension, 2) }} m²</p>
                            <p class="mb-1"><strong><i class="fas fa-mountain"></i> Tipo Suelo:</strong> 
                                <span class="badge" style="background-color: ${coloresSuelo['{{ $parcela->tipoSuelo }}'] || '#3498DB'}">{{ $parcela->tipoSuelo }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="fas fa-tractor"></i> Uso:</strong> {{ $parcela->usoSuelo }}</p>
                            <div class="text-center">
                                <a href="{{ route('parcelas.show', $parcela) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> Ver Detalles
                                </a>
                            </div>
                        </div>
                    `);

                    parcelasLayer.addLayer(layer);
                }
            } catch (error) {
                console.error('Error procesando parcela {{ $parcela->id }}:', error);
            }
        @endif
    @endforeach

    // Ajustar vista para mostrar todas las parcelas
    if (parcelasLayer.getLayers().length > 0) {
        var group = new L.featureGroup(parcelasLayer.getLayers());
        map.fitBounds(group.getBounds().pad(0.1));
    }

    // Control de capas
    var baseLayers = {
        "Satélite": L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles © Esri'
        }),
        "OpenStreetMap": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        })
    };

    var overlays = {
        "Parcelas": parcelasLayer
    };

    L.control.layers(baseLayers, overlays).addTo(map);
</script>

<style>
    .parcela-popup {
        min-width: 250px;
    }
    .parcela-popup h6 {
        color: #1B4332;
        margin-bottom: 0.5rem;
    }
    .info-box {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
</style>
@stop