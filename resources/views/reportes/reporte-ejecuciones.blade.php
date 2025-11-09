@extends('adminlte::page')

@section('title', 'Reporte de Ejecuciones Reales')

@section('content_header')
<h1 class="text-success fw-bold display-6">
    <i class="fas fa-tractor me-2"></i>Reporte de Ejecuciones Reales
</h1>
@stop

@section('content')
{{-- Botones superiores --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('reportes.index') }}" class="btn btn-success btn-lg shadow-sm">
        <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
    </a>
    <button id="btnPDF" class="btn btn-success btn-lg shadow-sm" disabled>
        <i class="fas fa-file-pdf me-2"></i> Exportar PDF
    </button>
</div>

{{-- Card filtros --}}
<div class="card shadow-lg border-0 mb-4">
    <div class="card-header bg-success text-white fw-bold py-3">
        <i class="fas fa-filter me-2"></i>Filtros Avanzados
    </div>

    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold text-success">Año</label>
                <select id="filtroAnio" class="form-select">
                    <option value="todos">Todos</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold text-success">Cultivo Plan</label>
                <select id="filtroCultivoPlan" class="form-select">
                    <option value="todos">Todos</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold text-success">Cultivo Real</label>
                <select id="filtroCultivoReal" class="form-select">
                    <option value="todos">Todos</option>
                </select>
            </div>

            @if(!auth()->user()->hasRole('Agricultor'))
            <div class="col-md-3">
                <label class="form-label fw-semibold text-success">Agricultor</label>
                <select id="filtroAgricultor" class="form-select">
                    <option value="todos">Todos</option>
                </select>
            </div>
            @endif
        </div>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold text-success">Parcela</label>
                <select id="filtroParcela" class="form-select">
                    <option value="todos">Todas</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold text-success">Éxito</label>
                <select id="filtroExito" class="form-select">
                    <option value="todos">Todos</option>
                    <option value="Exitoso">Exitoso</option>
                    <option value="Parcial">Parcial</option>
                    <option value="Fallido">Fallido</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold text-success">Rendimiento de Producción (%)</label>
                <select id="filtroRendimientoProduccion" class="form-select">
                    <option value="todos">Todos</option>
                    <option value="alto">Alto (≥80%)</option>
                    <option value="medio">Medio (60–79%)</option>
                    <option value="bajo">Bajo (&lt;60%)</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold text-success">Rendimiento de Parcela</label>
                <select id="filtroRendimientoParcela" class="form-select">
                    <option value="todos">Todos</option>
                    <option value="alto">Alto (≥8 unidad/ha)</option>
                    <option value="medio">Medio (4–7.9 unidad/ha)</option>
                    <option value="bajo">Bajo (&lt;4 unidad/ha)</option>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Alerta sin datos --}}
<div id="alertaSinDatos" class="alert alert-warning text-center fw-bold py-4 d-none">
    <i class="fas fa-exclamation-triangle me-2"></i>
    No se encontraron ejecuciones registradas.<br>
    Verifique que haya datos de siembra y cosecha disponibles.
</div>

{{-- Card tabla --}}
<div class="card shadow-lg border-0" id="contenedorTabla">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-table me-2"></i>Detalle de Ejecuciones
        </h5>
        <span class="badge bg-light text-success fw-bold" id="contadorResultados">0 registros</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaEjecuciones" class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead class="bg-success text-white">
                    <tr>
                        <th>Agricultor</th>
                        <th>Parcela</th>
                        <th>Cultivo Plan</th>
                        <th>Cultivo Real</th>
                        <th>Fecha Siembra</th>
                        <th>Fecha Cosecha</th>
                        <th>Sembrado</th>
                        <th>Cosechado</th>
                        <th>Unidad</th>
                        <th>Área (ha)</th>
                        <th>Rendimiento Producción (%)</th>
                        <th>Rendimiento Parcela</th>
                        <th>Éxito</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@stop

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<style>
.dt-buttons .btn {
    
    border-radius: 8px;
    font-size: 14px;
    padding: 6px 12px;
    border: none;
    color: white !important;
    font-weight: 600;
}
.dt-buttons .buttons-excel { background-color: #198754 !important; }
.dt-buttons .buttons-pdf { background-color: #dc3545 !important; }
.dt-buttons .buttons-print { background-color: #6c757d !important; }
.dt-buttons .btn:hover { opacity: 0.9; }
.card { border-radius: 12px; }
.table th { font-weight: 700; font-size: 0.95rem; }
</style>
@endpush

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

{{-- Librerías jsPDF para PDF manual --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<script>
let dataEjecuciones = [];
let tabla;

document.addEventListener('DOMContentLoaded', async () => {
    tabla = $('#tablaEjecuciones').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        responsive: true,
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm buttons-excel' },
            { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm buttons-pdf', orientation: 'landscape', pageSize: 'A4' },
            { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary btn-sm buttons-print' }
        ]
    });

    await cargarEjecuciones();
    inicializarFiltros();
    agregarEventosFiltros();

    // Exportar PDF manual
    document.getElementById('btnPDF').addEventListener('click', exportarPDF);
});

async function cargarEjecuciones() {
    const resp = await fetch(`{{ route('reportes.ejecuciones.data') }}`);
    dataEjecuciones = await resp.json();

    if (dataEjecuciones.length === 0) {
        document.getElementById('contenedorTabla').classList.add('d-none');
        document.getElementById('alertaSinDatos').classList.remove('d-none');
        return;
    }

    mostrarDatos(dataEjecuciones);
    document.getElementById('btnPDF').disabled = false;
}

function mostrarDatos(data) {
    tabla.clear();
    data.forEach(e => {
        const rendimientoProduccion = e.rendimiento_calculado
            ? e.rendimiento_calculado.toFixed(2) + '%'
            : '—';

        const rendimientoParcela = e.area_cultivada > 0 && e.cantidad_cosechada
            ? (e.cantidad_cosechada / e.area_cultivada).toFixed(2) + ' ' + (e.unidad_medida || '') + '/ha'
            : '—';

        tabla.row.add([
            e.agricultor,
            e.parcela,
            e.cultivo_plan,
            e.cultivo_real,
            e.fecha_siembra,
            e.fecha_cosecha,
            e.cantidad_sembrada,
            e.cantidad_cosechada,
            e.unidad_medida,
            e.area_cultivada,
            rendimientoProduccion,
            rendimientoParcela,
            e.fue_exitoso || '—',
            e.observaciones || '—'
        ]);
    });
    tabla.draw();
    document.getElementById('contadorResultados').textContent = `${data.length} registros`;
}

function inicializarFiltros() {
    const addOptions = (id, values) => {
        const select = document.getElementById(id);
        values.sort().forEach(v => select.innerHTML += `<option value="${v}">${v}</option>`);
    };

    addOptions('filtroAnio', [...new Set(dataEjecuciones.map(e => e.fecha_siembra?.split('/').pop()).filter(Boolean))]);
    addOptions('filtroCultivoPlan', [...new Set(dataEjecuciones.map(e => e.cultivo_plan).filter(v => v && v !== '—'))]);
    addOptions('filtroCultivoReal', [...new Set(dataEjecuciones.map(e => e.cultivo_real).filter(v => v && v !== '—'))]);
    if (document.getElementById('filtroAgricultor'))
        addOptions('filtroAgricultor', [...new Set(dataEjecuciones.map(e => e.agricultor).filter(v => v && v !== '—'))]);
    addOptions('filtroParcela', [...new Set(dataEjecuciones.map(e => e.parcela).filter(v => v && v !== '—'))]);
}

function agregarEventosFiltros() {
    document.querySelectorAll('select[id^="filtro"]').forEach(sel => sel.addEventListener('change', aplicarFiltros));
}

function aplicarFiltros() {
    let filtrados = [...dataEjecuciones];
    const f = id => document.getElementById(id)?.value || 'todos';

    filtrados = filtrados.filter(e => {
        const anio = e.fecha_siembra?.split('/').pop();
        const rendimientoProduccion = e.rendimiento_calculado || 0;
        const rendimientoParcela = e.area_cultivada > 0 && e.cantidad_cosechada ? e.cantidad_cosechada / e.area_cultivada : 0;

        return (f('filtroAnio') === 'todos' || anio === f('filtroAnio')) &&
               (f('filtroCultivoPlan') === 'todos' || e.cultivo_plan === f('filtroCultivoPlan')) &&
               (f('filtroCultivoReal') === 'todos' || e.cultivo_real === f('filtroCultivoReal')) &&
               (f('filtroAgricultor') === 'todos' || e.agricultor === f('filtroAgricultor')) &&
               (f('filtroParcela') === 'todos' || e.parcela === f('filtroParcela')) &&
               (f('filtroExito') === 'todos' || e.fue_exitoso === f('filtroExito')) &&
               (
                   f('filtroRendimientoProduccion') === 'todos' ||
                   (f('filtroRendimientoProduccion') === 'alto' && rendimientoProduccion >= 80) ||
                   (f('filtroRendimientoProduccion') === 'medio' && rendimientoProduccion >= 60 && rendimientoProduccion < 80) ||
                   (f('filtroRendimientoProduccion') === 'bajo' && rendimientoProduccion < 60)
               ) &&
               (
                   f('filtroRendimientoParcela') === 'todos' ||
                   (f('filtroRendimientoParcela') === 'alto' && rendimientoParcela >= 8) ||
                   (f('filtroRendimientoParcela') === 'medio' && rendimientoParcela >= 4 && rendimientoParcela < 8) ||
                   (f('filtroRendimientoParcela') === 'bajo' && rendimientoParcela < 4)
               );
    });

    mostrarDatos(filtrados);
}

// === Exportar PDF manual ===
function exportarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'pt', 'a4');
    doc.setFontSize(16).setTextColor(40, 167, 69);
    doc.text("Reporte de Ejecuciones Reales", 420, 40, { align: "center" });
    doc.setFontSize(11).setTextColor(0, 0, 0);
    doc.text(`Fecha de exportación: ${new Date().toLocaleDateString()}`, 40, 70);

    doc.autoTable({
        html: '#tablaEjecuciones',
        startY: 90,
        theme: 'grid',
        styles: { halign: 'center', valign: 'middle', fontSize: 8 },
        headStyles: { fillColor: [40, 167, 69], textColor: 255, fontStyle: 'bold' },
    });

    doc.save(`Ejecuciones_${new Date().toISOString().split('T')[0]}.pdf`);
}
</script>
@stop
