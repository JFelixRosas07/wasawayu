@extends('adminlte::page')

@section('title', 'Mapa General de Parcelas')

@section('content_header')
<h1 class="text-success fw-bold display-6">
    <i class="fas fa-map-marked-alt me-2"></i>Mapa General de Parcelas
</h1>
@stop

@section('content')
<div class="card shadow-lg border-0">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-leaf me-2"></i> Parcelas Registradas
            </h5>
            <a href="{{ route('parcelas.index') }}" class="btn btn-success btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Volver al Listado
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <!-- Estadísticas Rápidas -->
        @php
            // Formato limpio de superficie total (máx. 2 decimales)
            $superficieTotal = rtrim(rtrim(number_format($parcelas->sum('extension'), 2, '.', ''), '0'), '.');
        @endphp

        <div class="row m-3">
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-success shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-map"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Parcelas</span>
                        <span class="info-box-number">{{ $parcelas->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-info shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Agricultores</span>
                        <span class="info-box-number">{{ $parcelas->unique('agricultor_id')->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-warning shadow-sm">
                    <span class="info-box-icon"><i class="fas fa-ruler-combined"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Superficie Total</span>
                        <span class="info-box-number">{{ $superficieTotal }} ha</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="info-box bg-gradient-primary shadow-sm">
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
            <h6 class="fw-bold text-success"><i class="fas fa-key me-2"></i>Leyenda de Tipos de Suelo</h6>
            <div class="d-flex flex-wrap gap-3">
                <span class="badge rounded-pill" style="background-color: #F9E79F; color: #000;"><i class="fas fa-square"></i> Arenoso</span>
                <span class="badge rounded-pill" style="background-color: #A569BD; color: #fff;"><i class="fas fa-square"></i> Arcilloso</span>
                <span class="badge rounded-pill" style="background-color: #58D68D; color: #000;"><i class="fas fa-square"></i> Franco</span>
                <span class="badge rounded-pill" style="background-color: #7F8C8D; color: #fff;"><i class="fas fa-square"></i> Pedregoso</span>
                <span class="badge rounded-pill" style="background-color: #85C1E9; color: #000;"><i class="fas fa-square"></i> Limoso</span>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />

<style>
    .parcela-popup {
        min-width: 250px;
        font-size: 0.9rem;
    }

    .parcela-popup h6 {
        color: #1B4332;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .parcela-popup p {
        margin-bottom: 0.3rem;
    }

    .parcela-popup .btn-ver-detalles {
        background-color: #198754;
        color: #fff !important;
        border: none;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 6px 10px;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .parcela-popup .btn-ver-detalles:hover {
        background-color: #157347;
        transform: translateY(-1px);
    }

    .info-box {
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const coloresSuelo = {
        'Arenoso': '#F9E79F',
        'Arcilloso': '#A569BD',
        'Franco': '#58D68D',
        'Pedregoso': '#7F8C8D',
        'Limoso': '#85C1E9'
    };

    // Inicializar mapa centrado
    const map = L.map('map').setView([-17.5837, -65.7040], 15);

    // Capas base
    const googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }).addTo(map);

    const osmBase = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    });

    L.control.layers({ 'Satélite': googleSat, 'Mapa base': osmBase }).addTo(map);

    // Grupo de parcelas
    const parcelasLayer = L.featureGroup().addTo(map);

    // Renderizar cada parcela
    @foreach($parcelas as $parcela)
        @php
            $extensionDisplay = rtrim(rtrim(number_format($parcela->extension, 2, '.', ''), '0'), '.');
        @endphp

        @if(!empty($parcela->poligono))
            try {
                const poligonoData = {!! json_encode($parcela->poligono) !!};
                if (poligonoData && poligonoData.geometry) {
                    const colorSuelo = coloresSuelo['{{ $parcela->tipoSuelo }}'] || '#3498DB';

                    const layer = L.geoJSON(poligonoData, {
                        style: {
                            fillColor: colorSuelo,
                            color: '#2C3E50',
                            weight: 2,
                            fillOpacity: 0.6
                        }
                    });

                    // Popup informativo (sin área estimada duplicada)
                    layer.bindPopup(`
                        <div class="parcela-popup">
                            <h6><strong>{{ $parcela->nombre }}</strong></h6>
                            <hr class="my-2">
                            <p><strong><i class="fas fa-user me-1"></i>Agricultor:</strong> {{ $parcela->agricultor->name ?? 'No asignado' }}</p>
                            <p><strong><i class="fas fa-ruler-combined me-1"></i>Superficie:</strong> {{ $extensionDisplay }} ha</p>
                            <p><strong><i class="fas fa-mountain me-1"></i>Tipo Suelo:</strong>
                                <span class="badge" style="background-color:${colorSuelo}; color:#fff;">{{ $parcela->tipoSuelo }}</span>
                            </p>
                            <p><strong><i class="fas fa-tractor me-1"></i>Uso:</strong> {{ $parcela->usoSuelo }}</p>
                            <div class="text-center mt-2">
                                <a href="{{ route('parcelas.show', $parcela) }}" class="btn-ver-detalles">
                                    <i class="fas fa-eye me-1"></i> Ver Detalles
                                </a>
                            </div>
                        </div>
                    `);

                    layer.addTo(parcelasLayer);
                }
            } catch (error) {
                console.error('Error procesando parcela {{ $parcela->id }}:', error);
            }
        @endif
    @endforeach

    // Ajustar vista general
    if (parcelasLayer.getLayers().length > 0) {
        map.fitBounds(parcelasLayer.getBounds().pad(0.1));
    } else {
        map.setView([-17.5837, -65.7040], 13);
    }
});
</script>
@stop
