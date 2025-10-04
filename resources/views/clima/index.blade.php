@extends('adminlte::page')

@section('title', 'Monitoreo Climático - Wasawayu')

@section('content_header')
<h1><i class="fas fa-cloud-sun"></i> Monitoreo Climático</h1>
@stop

@section('content')
<div class="row">
    <!-- Panel de Clima Actual -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-cloud-sun"></i> Clima Actual</h5>
            </div>
            <div class="card-body">
                <!-- Selector de Ubicación -->
                <div class="mb-3">
                    <label class="form-label"><strong>Ubicación:</strong></label>
                    <select id="selector-ubicacion" class="form-select">
                        @foreach($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion['lat'] }},{{ $ubicacion['lon'] }}"
                                data-nombre="{{ $ubicacion['nombre'] }}">
                                {{ $ubicacion['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Widget de Clima -->
                <div id="clima-widget">
                    @if($datosClima)
                        <div class="text-center">
                            <img src="https://openweathermap.org/img/wn/{{ $datosClima['weather'][0]['icon'] }}@2x.png"
                                alt="{{ $datosClima['weather'][0]['description'] }}" class="mb-2">
                            <h2 class="text-primary">{{ round($datosClima['main']['temp']) }}°C</h2>
                            <p class="h5 text-capitalize">{{ $datosClima['weather'][0]['description'] }}</p>
                            <p class="text-muted">{{ $ubicaciones[0]['nombre'] }}</p>

                            <div class="row mt-3">
                                <div class="col-6">
                                    <small><i class="fas fa-temperature-high text-danger"></i> Máx:
                                        {{ round($datosClima['main']['temp_max']) }}°C</small>
                                </div>
                                <div class="col-6">
                                    <small><i class="fas fa-temperature-low text-info"></i> Mín:
                                        {{ round($datosClima['main']['temp_min']) }}°C</small>
                                </div>
                                <div class="col-6 mt-2">
                                    <small><i class="fas fa-tint text-primary"></i> Humedad:
                                        {{ $datosClima['main']['humidity'] }}%</small>
                                </div>
                                <div class="col-6 mt-2">
                                    <small><i class="fas fa-wind text-secondary"></i> Viento:
                                        {{ round($datosClima['wind']['speed'] * 3.6) }} km/h</small>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No se pudieron cargar los datos climáticos
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Alertas Climáticas -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Alertas Agrícolas</h6>
            </div>
            <div class="card-body">
                <div id="alertas-clima">
                    @forelse($alertas as $alerta)
                        <div
                            class="alert alert-{{ $alerta['nivel'] == 'alto' ? 'danger' : 'warning' }} alert-dismissible fade show">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="{{ $alerta['icono'] }} fa-2x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="alert-heading mb-1">{{ $alerta['mensaje'] }}</h6>
                                    <p class="mb-1 small">{{ $alerta['descripcion'] }}</p>
                                    <p class="mb-1 small"><strong>Condición:</strong> {{ $alerta['condicion'] }}</p>
                                    <p class="mb-0 small"><strong>Acción recomendada:</strong> {{ $alerta['accion'] }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center mb-0">
                            <i class="fas fa-check-circle text-success fa-2x mb-2"></i><br>
                            No hay alertas activas<br>
                            <small class="text-muted">Condiciones normales para actividades agrícolas</small>
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Pronóstico Extendido -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Pronóstico 5 Días</h5>
            </div>
            <div class="card-body">
                @if($pronostico && isset($pronostico['list']))
                    <div class="row">
                        @php
                            $dias = [];
                            foreach ($pronostico['list'] as $item) {
                                $fecha = date('Y-m-d', $item['dt']);
                                if (!isset($dias[$fecha]) && count($dias) < 5) {
                                    $dias[$fecha] = $item;
                                }
                            }
                        @endphp

                        @foreach($dias as $fecha => $dia)
                            <div class="col-md-2 col-4 mb-3">
                                <div class="text-center">
                                    <small class="d-block fw-bold">{{ date('D', $dia['dt']) }}</small>
                                    <small class="d-block text-muted">{{ date('d/m', $dia['dt']) }}</small>
                                    <img src="https://openweathermap.org/img/wn/{{ $dia['weather'][0]['icon'] }}.png"
                                        alt="{{ $dia['weather'][0]['description'] }}" class="my-2">
                                    <div class="small">
                                        <span class="text-primary">{{ round($dia['main']['temp_max']) }}°</span> /
                                        <span class="text-info">{{ round($dia['main']['temp_min']) }}°</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">
                        No se pudo cargar el pronóstico extendido
                    </div>
                @endif
            </div>
        </div>

        <!-- Mapa de Condiciones Climáticas -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-map"></i> Mapa Climático</h5>
            </div>
            <div class="card-body">
                <div id="mapa-clima" style="height: 300px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Adicionales -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="info-box bg-gradient-info">
            <span class="info-box-icon"><i class="fas fa-temperature-high"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Temperatura Promedio</span>
                <span class="info-box-number">{{ $datosClima ? round($datosClima['main']['temp']) : 'N/A' }}°C</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-gradient-success">
            <span class="info-box-icon"><i class="fas fa-tint"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Humedad</span>
                <span class="info-box-number">{{ $datosClima ? $datosClima['main']['humidity'] : 'N/A' }}%</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-gradient-warning">
            <span class="info-box-icon"><i class="fas fa-wind"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Velocidad Viento</span>
                <span class="info-box-number">{{ $datosClima ? round($datosClima['wind']['speed'] * 3.6) : 'N/A' }}
                    km/h</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-gradient-primary">
            <span class="info-box-icon"><i class="fas fa-eye"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Visibilidad</span>
                <span class="info-box-number">{{ $datosClima ? round($datosClima['visibility'] / 1000, 1) : 'N/A' }}
                    km</span>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<style>
    .info-box {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    #clima-widget img {
        width: 80px;
        height: 80px;
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Mapa climático con capa satelital
    var mapa = L.map('mapa-clima').setView([-17.582086, -65.705282], 13);

    // Capa Satelital (Esri World Imagery)
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
        maxZoom: 18
    }).addTo(mapa);

    // Marcador de ubicación central
    L.marker([-17.582086, -65.705282])
        .addTo(mapa)
        .bindPopup('Wasawayu Central<br>Temperatura: {{ $datosClima ? round($datosClima["main"]["temp"]) : "N/A" }}°C')
        .openPopup();

    // Cambiar ubicación del clima
    document.getElementById('selector-ubicacion').addEventListener('change', function () {
        const [lat, lon] = this.value.split(',');
        const nombre = this.options[this.selectedIndex].dataset.nombre;

        // Aquí puedes hacer una llamada AJAX para actualizar el clima
        fetch('{{ route("clima.ubicacion") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ lat: lat, lon: lon })
        })
            .then(response => response.json())
            .then(data => {
                if (data) {
                    actualizarWidgetClima(data, nombre);
                }
            });
    });

    function actualizarWidgetClima(datos, nombre) {
        const widget = document.getElementById('clima-widget');
        widget.innerHTML = `
            <div class="text-center">
                <img src="https://openweathermap.org/img/wn/${datos.weather[0].icon}@2x.png" 
                     alt="${datos.weather[0].description}" class="mb-2">
                <h2 class="text-primary">${Math.round(datos.main.temp)}°C</h2>
                <p class="h5 text-capitalize">${datos.weather[0].description}</p>
                <p class="text-muted">${nombre}</p>
                
                <div class="row mt-3">
                    <div class="col-6">
                        <small><i class="fas fa-temperature-high text-danger"></i> Máx: ${Math.round(datos.main.temp_max)}°C</small>
                    </div>
                    <div class="col-6">
                        <small><i class="fas fa-temperature-low text-info"></i> Mín: ${Math.round(datos.main.temp_min)}°C</small>
                    </div>
                    <div class="col-6 mt-2">
                        <small><i class="fas fa-tint text-primary"></i> Humedad: ${datos.main.humidity}%</small>
                    </div>
                    <div class="col-6 mt-2">
                        <small><i class="fas fa-wind text-secondary"></i> Viento: ${Math.round(datos.wind.speed * 3.6)} km/h</small>
                    </div>
                </div>
            </div>
        `;
    }
</script>
@stop