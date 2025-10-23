@extends('adminlte::page')

@section('title', 'Agregar Detalle de Rotación')

@section('css')
<style>
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
        <form action="{{ route('detalles.store', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}" 
              method="POST" id="formDetalle">
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
                        <option value="{{ $cultivo->id }}"
                                data-siembra="{{ $cultivo->epocaSiembra }}"
                                data-cosecha="{{ $cultivo->epocaCosecha }}"
                                data-dias="{{ $cultivo->diasCultivo }}"
                                data-recomendaciones="{{ $cultivo->recomendaciones }}">
                            {{ $cultivo->nombre }} ({{ $cultivo->categoria }})
                        </option>
                    @endforeach
                </select>
                @error('cultivo_id')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            {{-- Información del cultivo --}}
            <div id="infoCultivo" class="card border-info mt-3 d-none">
                <div class="card-header bg-info text-white py-2">
                    <i class="fas fa-leaf"></i> Información del cultivo seleccionado
                </div>
                <div class="card-body small" style="white-space: pre-line;">
                    <p><strong>Época de siembra:</strong> <span id="infoSiembra">N/D</span></p>
                    <p><strong>Época de cosecha:</strong> <span id="infoCosecha">N/D</span></p>
                    <p><strong>Duración estimada:</strong> <span id="infoDias">N/D</span> días</p>
                    <p><strong>Recomendaciones:</strong> <span id="infoRecomendaciones">N/D</span></p>
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

    // Diccionario de meses
    const meses = {
        'enero': 0, 'febrero': 1, 'marzo': 2, 'abril': 3,
        'mayo': 4, 'junio': 5, 'julio': 6, 'agosto': 7,
        'septiembre': 8, 'octubre': 9, 'noviembre': 10, 'diciembre': 11
    };

    // Mostrar información del cultivo
    cultivoSelect.addEventListener('change', () => {
        const selected = cultivoSelect.options[cultivoSelect.selectedIndex];
        if (selected.value) {
            infoBox.classList.remove('d-none');
            const siembra = selected.dataset.siembra || 'N/D';
            const cosecha = selected.dataset.cosecha || 'N/D';
            const dias = selected.dataset.dias || 'N/D';
            const recomendaciones = selected.dataset.recomendaciones || 'Ninguna';

            document.getElementById('infoSiembra').innerText = siembra;
            document.getElementById('infoCosecha').innerText = cosecha;
            document.getElementById('infoDias').innerText = dias;
            document.getElementById('infoRecomendaciones').innerText = recomendaciones;

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

    // Activar o desactivar modo descanso
    descanso.addEventListener('change', () => {
        cultivoSelect.disabled = descanso.checked;
        if (descanso.checked) {
            cultivoSelect.value = '';
            infoBox.classList.add('d-none');
            const anioBase = new Date().getFullYear() + ((parseInt(anioSelect.value) || 1) - 1);
            fechaInicio.value = `${anioBase}-01-01`;
            fechaFin.value = `${anioBase}-12-31`;
        } else {
            fechaInicio.value = '';
            fechaFin.value = '';
        }
    });

    // Validaciones al enviar
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
