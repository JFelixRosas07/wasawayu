@extends('adminlte::page')

@section('title', 'Rotaciones')

@section('content_header')
<h1><i class="fas fa-seedling"></i>
    {{ auth()->user()->hasRole('Agricultor') ? 'Mis Rotaciones' : 'Gestión de Rotaciones' }}
</h1>
@stop

@section('content')
<div class="row">
    <!-- Panel lateral: agricultores y parcelas -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    {{ auth()->user()->hasRole('Agricultor') ? 'Mis Parcelas y Planes' : 'Parcelas y Planes' }}
                </h5>
                @if(!auth()->user()->hasRole('Agricultor'))
                    <a href="{{ route('planes.index') }}" class="btn btn-sm btn-info">
                        <i class="fas fa-list"></i> Ver todos
                    </a>
                @endif
            </div>

            <div class="card-body">
                {{-- Búsqueda y selector solo para Admin/Técnico --}}
                @unless(auth()->user()->hasRole('Agricultor'))
                    <div class="mb-2">
                        <label for="busquedaAgricultor" class="fw-bold text-success">Buscar Agricultor</label>
                        <input type="text" id="busquedaAgricultor" class="form-control" placeholder="Ingrese nombre...">
                    </div>
                    <label for="filtro-agricultor"><strong>Elija un agricultor</strong></label>
                    <select id="filtro-agricultor" class="form-control">
                        <option value="">— Seleccione —</option>
                        @foreach($listaAgricultores as $a)
                            <option value="{{ $a->id }}" {{ (isset($agricultorId) && $agricultorId == $a->id) ? 'selected' : '' }}>
                                {{ $a->name }}
                            </option>
                        @endforeach
                    </select>
                    <hr>
                @endunless

                <div id="lista-jerarquica" style="max-height:520px; overflow:auto;">
                    @if(!$agricultores->count())
                                    <div class="text-center text-muted mt-3">
                                        <em>
                                            {{ auth()->user()->hasRole('Agricultor')
                        ? 'No tienes parcelas registradas aún.'
                        : 'Seleccione un agricultor para ver sus parcelas' }}
                                        </em>
                                    </div>
                    @endif

                    @foreach($agricultores as $ag)
                        <div class="mb-3">
                            <h5 class="mb-1">{{ $ag->name }}</h5>
                            @if($ag->parcelas->isEmpty())
                                <small class="text-muted">Sin parcelas</small>
                            @else
                                <ul class="list-unstyled">
                                    @foreach($ag->parcelas as $par)
                                        <li class="mb-2">
                                            <b>{{ $par->nombre }}</b>
                                            <div><small class="text-muted">ID: {{ $par->id }}</small></div>

                                            @php $ultimoPlan = $par->planes->first(); @endphp
                                            @if($ultimoPlan)
                                                            <div>
                                                                <small>Último plan: {{ $ultimoPlan->nombre }} —
                                                                    <span
                                                                        class="badge
                                                                                        {{ $ultimoPlan->estado == 'finalizado' ? 'badge-secondary' :
                                                ($ultimoPlan->estado == 'en_ejecucion' ? 'badge-success' : 'badge-info') }}">
                                                                        {{ ucfirst($ultimoPlan->estado) }}
                                                                    </span>
                                                                </small>
                                                            </div>
                                                            <div class="mt-1">
                                                                <a href="{{ route('planes.index', ['parcela_id' => $par->id]) }}"
                                                                    class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-list"></i> Ver Planes
                                                                </a>
                                                                @if($ultimoPlan->estado === 'finalizado' && !auth()->user()->hasRole('Agricultor'))
                                                                    <a href="{{ route('planes.create', ['parcela_id' => $par->id]) }}"
                                                                        class="btn btn-sm btn-outline-success">
                                                                        <i class="fas fa-plus"></i> Nuevo Plan
                                                                    </a>
                                                                @endif
                                                            </div>
                                            @else
                                                @unless(auth()->user()->hasRole('Agricultor'))
                                                    <div class="mt-1">
                                                        <a href="{{ route('planes.create', ['parcela_id' => $par->id]) }}"
                                                            class="btn btn-sm btn-outline-success">
                                                            <i class="fas fa-plus"></i> Crear Plan
                                                        </a>
                                                    </div>
                                                @endunless
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Panel derecho: mapa -->
    <div class="col-md-8">
        <div class="card card-primary">
            <div class="card-body p-0">
                <div id="rotaciones-map" style="height: 640px;"></div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<style>
    #rotaciones-map {
        width: 100%;
        height: 640px;
    }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('rotaciones-map').setView([-17.5837, -65.7040], 14);

        const satLayer = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
            maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
        }).addTo(map);

        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19, attribution: '© OpenStreetMap'
        });

        L.control.layers({ 'Satélite': satLayer, 'Mapa base': osmLayer }).addTo(map);

        const parcelStyle = feature => ({
            color: '#2c3e50',
            weight: 2.5,
            fillColor: '#27ae60',
            fillOpacity: 0.45
        });

        const parcelLayer = L.geoJSON(null, {
            style: parcelStyle,
            onEachFeature: (feature, layer) => {
                const p = feature.properties || {};
                const areaHa = turf.area(feature) / 10000;
                const areaTexto = areaHa.toFixed(2) + ' ha';
                const popupHtml = `
                <div style="font-size:16px; line-height:1.5">
                    <strong>🌾 ${p.nombre}</strong><br>
                    <small><b>Propietario:</b> ${p.agricultor}</small><br>
                    <small><b>Cultivo actual:</b> ${p.cultivo}</small><br>
                    <small><b>Área:</b> ${areaTexto}</small>
                </div>
            `;
                layer.bindPopup(popupHtml);
                layer.on({
                    mouseover: e => e.target.setStyle({ weight: 3.5, fillOpacity: 0.7 }),
                    mouseout: e => parcelLayer.resetStyle(e.target)
                });
            }
        }).addTo(map);

        function loadGeojson(agricultorId) {
            const url = "{{ route('admin.rotaciones.parcelas.geojson') }}" + (agricultorId ? `?agricultor_id=${agricultorId}` : '');
            fetch(url)
                .then(res => res.json())
                .then(data => {
                    parcelLayer.clearLayers().addData(data);
                    if (parcelLayer.getLayers().length) {
                        map.fitBounds(parcelLayer.getBounds(), { padding: [20, 20] });
                    } else {
                        map.setView([-17.5837, -65.7040], 13);
                    }
                })
                .catch(err => console.error('Error cargando parcelas:', err));
        }

        const esAgricultor = @json(auth()->user()->hasRole('Agricultor'));
        const select = document.getElementById('filtro-agricultor');

        if (!esAgricultor && select) {
            const busquedaInput = document.getElementById('busquedaAgricultor');
            busquedaInput.addEventListener('input', function () {
                const term = this.value.toLowerCase();
                const options = select.querySelectorAll('option');
                options.forEach(opt => {
                    if (opt.value === "") return;
                    opt.style.display = opt.textContent.toLowerCase().includes(term) ? 'block' : 'none';
                });
            });

            select.addEventListener('change', function () {
                const id = this.value;
                const url = new URL(window.location.href);
                id ? url.searchParams.set('agricultor_id', id) : url.searchParams.delete('agricultor_id');
                window.location.href = url;
            });
        }

        const agricultorSeleccionado = "{{ $agricultorId ?? '' }}";
        loadGeojson(agricultorSeleccionado || (esAgricultor ? "{{ auth()->id() }}" : null));
    });
</script>
@stop