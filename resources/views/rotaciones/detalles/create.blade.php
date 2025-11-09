@extends('adminlte::page')

@section('title', 'Agregar Detalle de Rotación')

@section('css')
<style>
    /* Estilo mejorado de tarjeta de información del cultivo */
    #infoCultivo {
        font-size: 1rem;
        color: #333;
    }

    #infoCultivo .card-header {
        background-color: #2e6b4d;
        font-weight: 600;
        font-size: 1.1rem;
    }

    #infoCultivo .card-body {
        background: #f9faf9;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    #infoCultivo img {
        max-height: 180px;
        object-fit: cover;
        border-radius: 0.5rem;
        border: 2px solid #dfe3df;
    }

    #infoCultivo h5 {
        color: #2e6b4d;
        font-weight: 600;
        font-size: 1.1rem;
    }

    /* Quitar movimiento de hover */
    .card:hover,
    .info-card:hover {
        transform: none !important;
        transition: none !important;
    }
</style>
@stop

@section('content_header')
<h1><i class="fas fa-plus-circle text-success"></i> Agregar detalle al Plan: {{ $plan->nombre }}</h1>
@stop

@section('content')
@php
    $parcelaId = request('parcela_id');
@endphp

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('planes.show', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}"
        class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Volver al Plan
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('detalles.store', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}" method="POST"
            id="formDetalle">
            @csrf

            {{-- Año --}}
            <div class="form-group">
                <label for="anio"><i class="fas fa-calendar"></i> Año del ciclo *</label>
                @php
                    $detalles = $plan->detalles ?? collect();
                    $aniosUsados = $detalles->pluck('anio')->map(fn($v) => (int) $v)->toArray();
                    $totalAnios = (int) ($plan->anios ?? 4);
                @endphp
                <select name="anio" id="anio" class="form-control" required>
                    <option value="">-- Seleccionar año --</option>
                    @for ($i = 1; $i <= $totalAnios; $i++)
                        @if(!in_array($i, $aniosUsados))
                            <option value="{{ $i }}">Año {{ $i }}</option>
                        @endif
                    @endfor
                </select>
            </div>

            {{-- Cultivo --}}
            <div class="form-group">
                <label for="cultivo_id"><i class="fas fa-seedling"></i> Cultivo</label>
                <select name="cultivo_id" id="cultivo_id" class="form-control">
                    <option value="">-- Seleccionar cultivo --</option>
                    @foreach($cultivos as $cultivo)
                        @php
                            $imagenCultivo = $cultivo->imagen
                                ? (Str::startsWith($cultivo->imagen, 'http')
                                    ? $cultivo->imagen
                                    : asset($cultivo->imagen))
                                : asset('img/no-image.png');
                        @endphp
                        <option value="{{ $cultivo->id }}" data-siembra="{{ $cultivo->epocaSiembra }}"
                            data-cosecha="{{ $cultivo->epocaCosecha }}" data-dias="{{ $cultivo->diasCultivo }}"
                            data-recomendaciones="{{ $cultivo->recomendaciones }}" data-imagen="{{ $imagenCultivo }}">
                            {{ $cultivo->nombre }} ({{ $cultivo->categoria }})
                        </option>
                    @endforeach
                </select>
                @error('cultivo_id')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            {{-- Información del cultivo mejorada --}}
            <div id="infoCultivo" class="card shadow-sm border-success mt-3 d-none">
                <div class="card-header text-white py-2">
                    <i class="fas fa-leaf"></i> Información del cultivo seleccionado
                </div>

                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <img id="infoImagen" src="" alt="Imagen del cultivo"
                                class="img-fluid rounded shadow-sm border">
                            <h5 id="infoNombre" class="mt-2">Nombre del cultivo</h5>
                        </div>

                        <div class="col-md-8">
                            <p><strong>🌱 Época de siembra:</strong> <span id="infoSiembra"
                                    class="text-muted">N/D</span></p>
                            <p><strong>🌾 Época de cosecha:</strong> <span id="infoCosecha"
                                    class="text-muted">N/D</span></p>
                            <p><strong>⏳ Duración estimada:</strong> <span id="infoDias" class="text-muted">N/D</span>
                                días</p>
                            <p><strong>💡 Recomendaciones:</strong> <span id="infoRecomendaciones"
                                    class="text-muted">N/D</span></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Descanso --}}
            <div class="form-check my-3">
                <input type="hidden" name="es_descanso" value="0">
                <input type="checkbox" name="es_descanso" id="es_descanso" value="1" class="form-check-input">
                <label for="es_descanso" class="form-check-label">Este año es descanso</label>
            </div>

            {{-- Fechas --}}
            <div class="row">
                <div class="col-md-6">
                    <label><i class="fas fa-calendar-day"></i> Fecha de inicio planificada *</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label><i class="fas fa-calendar-check"></i> Fecha de fin planificada *</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required>
                </div>
            </div>

            {{-- Botones --}}
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Detalle
                </button>
                <a href="{{ route('planes.show', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}"
                    class="btn btn-outline-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cultivoSelect = document.getElementById('cultivo_id');
        const infoBox = document.getElementById('infoCultivo');
        const descanso = document.getElementById('es_descanso');
        const fechaInicio = document.getElementById('fecha_inicio');
        const fechaFin = document.getElementById('fecha_fin');
        const anioSelect = document.getElementById('anio');
        const form = document.getElementById('formDetalle');

        // Seleccionar automáticamente el primer año disponible (NO TOCAR)
        if (anioSelect && !anioSelect.value) {
            const firstAvailable = Array.from(anioSelect.options).find(opt => opt.value !== '');
            if (firstAvailable) {
                anioSelect.value = firstAvailable.value;
            }
        }

        const meses = {
            'enero': 0, 'febrero': 1, 'marzo': 2, 'abril': 3,
            'mayo': 4, 'junio': 5, 'julio': 6, 'agosto': 7,
            'septiembre': 8, 'octubre': 9, 'noviembre': 10, 'diciembre': 11
        };

        cultivoSelect.addEventListener('change', () => {
            const selected = cultivoSelect.options[cultivoSelect.selectedIndex];
            const imgElement = document.getElementById('infoImagen');
            const nombreLabel = document.getElementById('infoNombre');

            if (selected.value) {
                infoBox.classList.remove('d-none');
                const siembra = selected.dataset.siembra || 'N/D';
                const cosecha = selected.dataset.cosecha || 'N/D';
                const dias = selected.dataset.dias || 'N/D';
                const recomendaciones = selected.dataset.recomendaciones || 'Ninguna';
                const imagen = selected.dataset.imagen || '';
                const nombre = selected.textContent.trim();

                document.getElementById('infoSiembra').innerText = siembra;
                document.getElementById('infoCosecha').innerText = cosecha;
                document.getElementById('infoDias').innerText = dias;
                document.getElementById('infoRecomendaciones').innerText = recomendaciones;
                nombreLabel.innerText = nombre;

                imgElement.src = imagen || "{{ asset('img/no-image.png') }}";
                imgElement.alt = imagen ? "Cultivo: " + nombre : "Sin imagen disponible";

                const anioBase = new Date().getFullYear() + ((parseInt(anioSelect.value) || 1) - 1);
                const inicioMes = Object.keys(meses).find(m => siembra.toLowerCase().includes(m));
                const finMes = Object.keys(meses).find(m => cosecha.toLowerCase().includes(m));

                if (inicioMes && finMes) {
                    const mesInicio = meses[inicioMes];
                    const mesFin = meses[finMes];
                    let anioFin = anioBase;
                    if (mesFin < mesInicio) anioFin += 1;
                    fechaInicio.value = new Date(anioBase, mesInicio, 1).toISOString().split('T')[0];
                    fechaFin.value = new Date(anioFin, mesFin + 1, 0).toISOString().split('T')[0];
                }
            } else {
                infoBox.classList.add('d-none');
                fechaInicio.value = '';
                fechaFin.value = '';
            }
        });

        descanso.addEventListener('change', () => {
            cultivoSelect.disabled = descanso.checked;
            if (descanso.checked) {
                cultivoSelect.value = '';
                infoBox.classList.add('d-none');
                const anioBase = new Date().getFullYear() + ((parseInt(anioSelect.value) || 1) - 1);
                fechaInicio.value = `${anioBase}-01-01`;
                fechaFin.value = `${anioBase}-12-31`;
            } else {
                cultivoSelect.disabled = false;
                fechaInicio.value = '';
                fechaFin.value = '';
            }
        });

        form.addEventListener('submit', (e) => {
            if (!descanso.checked && !cultivoSelect.value) {
                e.preventDefault();
                alert("Debe seleccionar un cultivo o marcar el año como descanso.");
                return;
            }
            const inicio = new Date(fechaInicio.value);
            const fin = new Date(fechaFin.value);
            if (fechaInicio.value && fechaFin.value && inicio > fin) {
                e.preventDefault();
                alert("La fecha de inicio debe ser anterior o igual a la fecha de fin.");
            }
        });
    });
</script>
@stop