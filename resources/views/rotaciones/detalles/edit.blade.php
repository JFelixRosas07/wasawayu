@extends('adminlte::page')

@section('title', 'Editar Detalle de Rotación')

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
    </style>
@stop

@section('content_header')
    <h1><i class="fas fa-edit text-primary"></i> Editar detalle del Plan: {{ $detalle->plan->nombre }}</h1>
@stop

@section('content')
    @php
        $parcelaId = request('parcela_id');
    @endphp

    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('planes.show', ['plan_id' => $detalle->plan->id, 'parcela_id' => $parcelaId]) }}"
            class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Plan
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('detalles.update', ['detalle' => $detalle->id, 'parcela_id' => $parcelaId]) }}"
                method="POST" id="formDetalle">
                @csrf
                @method('PUT')

                {{-- Año (bloqueado) --}}
                <div class="form-group">
                    <label for="anio"><i class="fas fa-calendar"></i> Año del ciclo *</label>
                    <input type="number" name="anio" id="anio"
                        class="form-control @error('anio') is-invalid @enderror" value="{{ old('anio', $detalle->anio) }}"
                        readonly>
                    @error('anio')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Este valor no puede modificarse.</small>
                </div>

                {{-- Cultivo --}}
                <div class="form-group">
                    <label for="cultivo_id"><i class="fas fa-seedling"></i> Cultivo</label>
                    <select name="cultivo_id" id="cultivo_id"
                        class="form-control @error('cultivo_id') is-invalid @enderror">
                        <option value="">-- Seleccionar cultivo --</option>
                        @foreach ($cultivos as $cultivo)
                            @php
                                // Generar la URL correcta para la imagen del cultivo
                                $imagenCultivo = $cultivo->imagen
                                    ? (Str::startsWith($cultivo->imagen, 'http')
                                        ? $cultivo->imagen
                                        : asset($cultivo->imagen))
                                    : asset('img/no-image.png');
                            @endphp

                            <option value="{{ $cultivo->id }}" data-siembra="{{ $cultivo->epocaSiembra }}"
                                data-cosecha="{{ $cultivo->epocaCosecha }}" data-dias="{{ $cultivo->diasCultivo }}"
                                data-recomendaciones="{{ $cultivo->recomendaciones }}" data-imagen="{{ $imagenCultivo }}"
                                {{ old('cultivo_id', $detalle->cultivo_id) == $cultivo->id && !$detalle->es_descanso ? 'selected' : '' }}>
                                {{ $cultivo->nombre }} ({{ $cultivo->categoria }})
                            </option>
                        @endforeach

                    </select>
                    @error('cultivo_id')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <small class="form-text text-muted">Selecciona un cultivo o marca “descanso”.</small>
                </div>

                {{-- Información del cultivo --}}
                <div id="infoCultivo"
                    class="card shadow-sm border-success mt-3 {{ $detalle->cultivo_id ? '' : 'd-none' }}">
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
                    <input type="checkbox" name="es_descanso" id="es_descanso" value="1"
                        class="form-check-input @error('es_descanso') is-invalid @enderror"
                        {{ old('es_descanso', $detalle->es_descanso) ? 'checked' : '' }}>
                    <label for="es_descanso" class="form-check-label">Este año es descanso</label>
                    @error('es_descanso')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Fechas --}}
                <div class="row">
                    <div class="col-md-6">
                        <label><i class="fas fa-calendar-day"></i> Fecha de inicio planificada *</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                            class="form-control @error('fecha_inicio') is-invalid @enderror"
                            value="{{ old('fecha_inicio', $detalle->fecha_inicio?->format('Y-m-d')) }}" required>
                        @error('fecha_inicio')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label><i class="fas fa-calendar-check"></i> Fecha de fin planificada *</label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                            class="form-control @error('fecha_fin') is-invalid @enderror"
                            value="{{ old('fecha_fin', $detalle->fecha_fin?->format('Y-m-d')) }}" required>
                        @error('fecha_fin')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Botones --}}
                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Actualizar Detalle
                    </button>
                    <a href="{{ route('planes.show', ['plan_id' => $detalle->plan->id, 'parcela_id' => $parcelaId]) }}"
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
            const anioInput = document.getElementById('anio');
            const form = document.getElementById('formDetalle');

            const meses = {
                'enero': 0,
                'febrero': 1,
                'marzo': 2,
                'abril': 3,
                'mayo': 4,
                'junio': 5,
                'julio': 6,
                'agosto': 7,
                'septiembre': 8,
                'octubre': 9,
                'noviembre': 10,
                'diciembre': 11
            };

            function actualizarInfoCultivo() {
                const selected = cultivoSelect.options[cultivoSelect.selectedIndex];
                const imgElement = document.getElementById('infoImagen');
                const nombreLabel = document.getElementById('infoNombre');

                if (selected && selected.value) {
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

                    if (imagen) {
                        imgElement.src = imagen;
                        imgElement.alt = "Cultivo: " + nombre;
                    } else {
                        imgElement.src = "{{ asset('img/no-image.png') }}";
                        imgElement.alt = "Sin imagen disponible";
                    }

                    if (!fechaInicio.value || !fechaFin.value) {
                        const anioBase = new Date().getFullYear() + ((parseInt(anioInput.value) || 1) - 1);
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
                    }
                } else {
                    infoBox.classList.add('d-none');
                }
            }

            cultivoSelect.addEventListener('change', actualizarInfoCultivo);

            descanso.addEventListener('change', () => {
                cultivoSelect.disabled = descanso.checked;
                if (descanso.checked) {
                    cultivoSelect.value = '';
                    infoBox.classList.add('d-none');
                    const anioBase = new Date().getFullYear() + ((parseInt(anioInput.value) || 1) - 1);
                    fechaInicio.value = `${anioBase}-01-01`;
                    fechaFin.value = `${anioBase}-12-31`;
                } else {
                    cultivoSelect.disabled = false;
                }
            });

            if (cultivoSelect.value && !descanso.checked) {
                actualizarInfoCultivo();
            }

            if (descanso.checked) {
                cultivoSelect.disabled = true;
                const anioBase = new Date().getFullYear() + ((parseInt(anioInput.value) || 1) - 1);
                fechaInicio.value = `${anioBase}-01-01`;
                fechaFin.value = `${anioBase}-12-31`;
            }

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
