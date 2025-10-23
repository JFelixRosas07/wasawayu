@extends('adminlte::page')

@section('title', 'Reporte de Parcelas por Agricultor')

@section('content_header')
<h1 class="text-success fw-bold">
    <i class="fas fa-map-marked-alt me-2"></i>Reporte de Parcelas por Agricultor
</h1>
@stop

@section('content')
@php
    use Illuminate\Support\Facades\Auth;
    $usuario = Auth::user();
@endphp

{{-- Botones principales --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('reportes.index') }}" class="btn btn-success btn-lg">
        <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
    </a>
    <div>
        <button id="btnPDF" class="btn btn-success btn-lg" disabled>
            <i class="fas fa-file-pdf me-1"></i> Exportar PDF
        </button>
    </div>
</div>

{{-- Panel de filtros --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-success text-white py-2 d-flex align-items-center">
        <i class="fas fa-filter me-2"></i>
        <span class="fw-semibold">Filtros de Búsqueda</span>
    </div>
    <div class="card-body py-3">
        <div class="row align-items-end g-3">
            <div class="col-md-8">
                <label for="agricultor" class="form-label text-success fw-semibold mb-1">
                    <i class="fas fa-user me-1"></i> Agricultor
                </label>
                <select id="agricultor" class="form-select form-select-sm" autocomplete="off"
                    @if($usuario->hasRole('Agricultor')) disabled @endif>
                    <option value="">Seleccione...</option>
                    @if($usuario->hasRole(['Administrador', 'TecnicoAgronomo']))
                        <option value="todos">Todos los Agricultores</option>
                        @foreach ($agricultores as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    @else
                        <option value="{{ $usuario->id }}" selected>{{ $usuario->name }}</option>
                    @endif
                </select>
                <small class="text-muted">Seleccione un agricultor o "Todos".</small>
            </div>
            <div class="col-md-4 text-end">
                <button id="btnMostrar" class="btn btn-success btn-sm px-4"
                    @if($usuario->hasRole('Agricultor')) @else disabled @endif>
                    <i class="fas fa-search me-1"></i> Mostrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Panel de resultados --}}
<div id="tablaParcelas" class="mt-4" style="display:none;">
    {{-- Resumen --}}
    <div class="card border-success shadow-sm mb-4">
        <div class="card-header bg-light border-success py-3">
            <h5 class="mb-0 text-success fw-bold">
                <i class="fas fa-chart-bar me-2"></i>Resumen Estadístico
                <span id="tituloAgricultor" class="badge bg-success fs-6 ms-2"></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row text-center" id="resumenEstadistico"></div>
        </div>
    </div>

    {{-- Tabla detallada --}}
    <div class="card shadow-lg border-0">
        <div class="card-header bg-success text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 fw-bold">
                    <i class="fas fa-table me-2"></i>Detalle de Parcelas
                </h4>
                <span class="badge bg-light text-success fs-6" id="contadorParcelas"></span>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover align-middle mb-0" id="tablaDatos">
                    <thead class="bg-success text-white">
                        <tr>
                            <th>Agricultor</th>
                            <th>Nombre Parcela</th>
                            <th>Superficie (ha)</th>
                            <th>Ubicación</th>
                            <th>Tipo de Suelo</th>
                            <th>Estado</th>
                            <th>Planes de Rotación</th>
                        </tr>
                    </thead>
                    <tbody id="parcelasBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Sin datos --}}
<div id="sinDatos" class="card border-warning text-center py-5 mt-4" style="display:none;">
    <div class="card-body">
        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
        <h4 class="text-warning">No se encontraron parcelas</h4>
        <p class="text-muted">No hay registros disponibles para el agricultor seleccionado.</p>
    </div>
</div>
@stop

@section('js')
{{-- jsPDF --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

{{-- DataTables + Buttons --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const agricultor = document.getElementById('agricultor');
    const btnMostrar = document.getElementById('btnMostrar');
    const parcelasBody = document.getElementById('parcelasBody');
    const tablaParcelas = document.getElementById('tablaParcelas');
    const sinDatos = document.getElementById('sinDatos');
    const tituloAgricultor = document.getElementById('tituloAgricultor');
    const contadorParcelas = document.getElementById('contadorParcelas');
    const resumenEstadistico = document.getElementById('resumenEstadistico');
    const btnPDF = document.getElementById('btnPDF');

    agricultor.addEventListener('change', () => {
        btnMostrar.disabled = !agricultor.value;
        tablaParcelas.style.display = 'none';
        sinDatos.style.display = 'none';
        btnPDF.disabled = true;
    });

    btnMostrar.addEventListener('click', async function () {
        if (!agricultor.value) return;
        btnMostrar.disabled = true;
        btnMostrar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Cargando...';

        try {
            const res = await fetch(`/reportes/parcelas/${agricultor.value}`);
            const data = await res.json();

            tituloAgricultor.textContent = agricultor.value === 'todos'
                ? 'Todos los Agricultores'
                : agricultor.options[agricultor.selectedIndex].text;

            if (!data || data.length === 0) {
                mostrarSinDatos();
            } else {
                mostrarDatos(data);
            }
        } catch (err) {
            console.error('Error:', err);
            mostrarError();
        } finally {
            btnMostrar.disabled = false;
            btnMostrar.innerHTML = '<i class="fas fa-search me-2"></i> Mostrar';
        }
    });

    function mostrarSinDatos() {
        tablaParcelas.style.display = 'none';
        sinDatos.style.display = 'block';
        btnPDF.disabled = true;
    }

    function mostrarError() {
        parcelasBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i><br>
                    Error al cargar los datos. Intente nuevamente.
                </td>
            </tr>`;
        tablaParcelas.style.display = 'block';
        sinDatos.style.display = 'none';
    }

    function mostrarDatos(data) {
        const total = data.length;
        const totalSup = data.reduce((sum, p) => sum + (parseFloat(p.superficie) || 0), 0);
        const conPlanes = data.filter(p => p.planes && p.planes.length > 0).length;

        resumenEstadistico.innerHTML = `
            <div class="col-md-3 mb-3"><div class="card border-success border-2"><div class="card-body"><h3 class="text-success">${total}</h3><p class="text-muted mb-0">Total Parcelas</p></div></div></div>
            <div class="col-md-3 mb-3"><div class="card border-primary border-2"><div class="card-body"><h3 class="text-primary">${totalSup.toFixed(2)}</h3><p class="text-muted mb-0">Superficie Total (ha)</p></div></div></div>
            <div class="col-md-3 mb-3"><div class="card border-info border-2"><div class="card-body"><h3 class="text-info">${conPlanes}</h3><p class="text-muted mb-0">Parcelas con Planes</p></div></div></div>
            <div class="col-md-3 mb-3"><div class="card border-warning border-2"><div class="card-body"><h3 class="text-warning">${((conPlanes / total) * 100).toFixed(1)}%</h3><p class="text-muted mb-0">Cobertura de Planes</p></div></div></div>
        `;

        contadorParcelas.textContent = `${total} parcelas`;

        parcelasBody.innerHTML = data.map(p => `
            <tr>
                <td class="fw-bold">${p.agricultor.nombre}</td>
                <td><strong class="text-success">${p.nombre}</strong>${p.codigo ? `<br><small class="text-muted">Código: ${p.codigo}</small>` : ''}</td>
                <td class="fw-bold text-primary">${p.superficie ? `${parseFloat(p.superficie).toFixed(2)} ha` : '—'}</td>
                <td><i class="fas fa-map-marker-alt text-danger me-1"></i>${p.ubicacion || '—'}</td>
                <td><span class="badge bg-info">${p.tipo_suelo || 'No especificado'}</span></td>
                <td><span class="badge ${p.estado === 'Activa' ? 'bg-success' : 'bg-warning'}">${p.estado || 'Activa'}</span></td>
                <td>${p.planes && p.planes.length ? `<span class="badge bg-success">${p.planes.length} planes</span><br><small>${p.planes.join(', ')}</small>` : '<span class="badge bg-secondary">Sin planes</span>'}</td>
            </tr>
        `).join('');

        tablaParcelas.style.display = 'block';
        sinDatos.style.display = 'none';
        btnPDF.disabled = false;

        // Reiniciar DataTable si ya existe
        if ($.fn.DataTable.isDataTable('#tablaDatos')) {
            $('#tablaDatos').DataTable().destroy();
        }

        // Inicializar DataTable
        $('#tablaDatos').DataTable({
            pageLength: 10,
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
            order: [[1, 'asc']]
        });
    }

    // Exportar PDF manual
    btnPDF.addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');
        doc.setFontSize(18).setTextColor(40, 167, 69);
        doc.text("Reporte de Parcelas Agrícolas", 420, 40, { align: "center" });
        doc.setFontSize(11).setTextColor(0, 0, 0);
        doc.text(`Agricultor: ${tituloAgricultor.textContent}`, 40, 70);
        doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 40, 85);
        doc.text(`Total: ${contadorParcelas.textContent}`, 40, 100);

        doc.autoTable({
            html: '#tablaDatos',
            startY: 120,
            theme: 'grid',
            styles: { halign: 'center', valign: 'middle', fontSize: 9 },
            headStyles: { fillColor: [40, 167, 69], textColor: 255 }
        });

        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8).setTextColor(150);
            doc.text(`Página ${i} de ${pageCount} - Sistema Wasawayu`, 420, doc.internal.pageSize.height - 20, { align: "center" });
        }

        doc.save(`Reporte_Parcelas_${tituloAgricultor.textContent.replace(/\s+/g, '_')}.pdf`);
    });

    // Si es agricultor, auto-selecciona su nombre
    @if($usuario->hasRole('Agricultor'))
        document.getElementById('agricultor').value = "{{ $usuario->id }}";
        document.getElementById('btnMostrar').disabled = false;
    @endif
});
</script>

<style>
.card { border-radius: 12px; }
.table th { font-weight: 700; font-size: 0.95rem; }
.btn-lg { font-size: 1.1rem; padding: 0.75rem 1.5rem; }
.badge { font-size: 0.8rem; }
@media (max-width: 768px) {
    .btn-lg { font-size: 1rem; padding: 0.6rem 1.2rem; }
    .table-responsive { font-size: 0.9rem; }
}
</style>
@stop
