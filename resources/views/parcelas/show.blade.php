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
            @php
                // Formato limpio: máximo 2 decimales y quitar ceros innecesarios
                $extensionDisplay = rtrim(rtrim(number_format($parcela->extension, 2, '.', ''), '0'), '.');
            @endphp

            <div class="col-md-6">
                <p><strong><i class="fas fa-user text-success"></i> Agricultor:</strong>
                    {{ $parcela->agricultor->name ?? 'No asignado' }}
                </p>
                <p><strong><i class="fas fa-signature text-primary"></i> Nombre:</strong>
                    {{ $parcela->nombre }}
                </p>
                <p><strong><i class="fas fa-ruler-combined text-warning"></i> Superficie:</strong>
                    {{ $extensionDisplay }} ha
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
        <div id="map" style="height:420px; border-radius:10px;"></div>
    </div>
</div>

{{-- Botones de Acción --}}
<div class="mt-4 d-flex justify-content-between">
    <div>
        @if(auth()->user()->hasAnyRole(['Administrador', 'TecnicoAgronomo']))
            <a href="{{ route('parcelas.edit', $parcela) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar Parcela
            </a>
        @endif
    </div>
    <div>
        <a href="{{ route('parcelas.mapa-general') }}" class="btn btn-info">
            <i class="fas fa-map-marked-alt"></i> Ver Mapa General
        </a>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/custom.css') }}">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
    .card { border: none; border-radius: 10px; }
    .card-header { border-radius: 10px 10px 0 0 !important; }
    .btn { border-radius: 6px; margin-left: 5px; }

    .parcela-popup {
        min-width: 250px;
        font-size: 0.9rem;
    }
    .parcela-popup h6 {
        color: #1B4332;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }
    .parcela-popup p { margin-bottom: 0.3rem; }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
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

    const googleSat = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0','mt1','mt2','mt3']
    }).addTo(map);

    const osmBase = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    });

    L.control.layers({ 'Satélite': googleSat, 'Mapa base': osmBase }).addTo(map);

    const poligono = {!! json_encode($parcela->poligono) !!};
    const extensionDisplay = {!! json_encode($extensionDisplay) !!};

    if (poligono) {
        const colorSuelo = coloresSuelo['{{ $parcela->tipoSuelo }}'] || '#3498DB';
        const layer = L.geoJSON(poligono, {
            style: {
                fillColor: colorSuelo,
                color: '#2C3E50',
                weight: 2,
                fillOpacity: 0.6
            }
        }).addTo(map);

        map.fitBounds(layer.getBounds().pad(0.1));

        const popupContent = `
            <div class="parcela-popup">
                <h6><strong>{{ $parcela->nombre }}</strong></h6>
                <hr class="my-2">
                <p><strong><i class="fas fa-user me-1"></i>Agricultor:</strong> {{ $parcela->agricultor->name ?? 'No asignado' }}</p>
                <p><strong><i class="fas fa-ruler-combined me-1"></i>Superficie:</strong> ${extensionDisplay} ha</p>
                <p><strong><i class="fas fa-mountain me-1"></i>Tipo Suelo:</strong>
                    <span class="badge" style="background-color:${colorSuelo}; color:#fff;">{{ $parcela->tipoSuelo }}</span>
                </p>
                <p><strong><i class="fas fa-tractor me-1"></i>Uso:</strong> {{ $parcela->usoSuelo }}</p>
            </div>
        `;
        layer.bindPopup(popupContent);
    }
});
</script>
@stop
