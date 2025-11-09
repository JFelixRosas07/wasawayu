@extends('adminlte::page')

@section('title', 'Reporte de Rotación por Agricultor')

@section('content_header')
<h1 class="text-success fw-bold display-6">
    <i class="fas fa-sync-alt me-2"></i>Reporte de Rotación por Agricultor
</h1>
@stop

@section('content')
{{-- Encabezado de acciones --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('reportes.index') }}" class="btn btn-success btn-lg">
        <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
    </a>
    <button id="btnPDF" class="btn btn-success btn-lg" style="display:none;">
        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
    </button>
</div>

{{-- Filtros --}}
<div class="card shadow-lg border-0 mb-4">
    <div class="card-header bg-success text-white fw-bold py-3">
        <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
    </div>
    <div class="card-body py-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold text-success mb-1">Agricultor</label>
                <select id="agricultor" class="form-select form-select-sm" autocomplete="off">
                    <option value="">Seleccione...</option>

                    @php $usuario = auth()->user(); @endphp
                    @if($usuario->hasRole('Agricultor'))
                        <option value="{{ $usuario->id }}">{{ $usuario->name }}</option>
                    @else
                        @foreach ($agricultores as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold text-success mb-1">Parcela</label>
                <select id="parcela" class="form-select form-select-sm" disabled autocomplete="off">
                    <option value="">Seleccione agricultor</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold text-success mb-1">Plan de Rotación</label>
                <select id="plan" class="form-select form-select-sm" disabled autocomplete="off">
                    <option value="">Seleccione parcela</option>
                </select>
            </div>

            <div class="col-md-1 text-end">
                <button id="btnMostrar" class="btn btn-success btn-sm w-100" disabled title="Mostrar">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Resultados --}}
<div id="areaPDF" style="display:none;">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-success text-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="fas fa-seedling me-2"></i>Detalles del Plan de Rotación</span>
            <span id="tituloPlan" class="small"></span>
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">
                <strong>Agricultor:</strong> <span id="impresionAgricultor"></span> |
                <strong>Parcela:</strong> <span id="impresionParcela"></span> |
                <strong>Plan:</strong> <span id="impresionPlan"></span>
            </p>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover align-middle text-center mb-0" id="tablaDatos">
                    <thead class="bg-success text-white">
                        <tr>
                            <th>Parcela</th>
                            <th>Año</th>
                            <th>Cultivo</th>
                            <th>Descanso</th>
                            <th>Fechas</th>
                            <th>Ejecución</th>
                        </tr>
                    </thead>
                    <tbody id="detallesBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Panel sin datos --}}
<div id="sinDatos" class="card border-warning text-center py-5 mt-4" style="display:none;">
    <div class="card-body">
        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
        <h4 class="text-warning">No se encontraron detalles</h4>
        <p class="text-muted">No hay registros disponibles para el plan seleccionado.</p>
    </div>
</div>
@stop

{{-- === Estilos iguales al reporte de cultivos === --}}
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<style>
/* === Botones DataTables === */
.dt-buttons .btn {
    margin-right: 5px;
    border-radius: 4px;
    font-size: 14px;
    padding: 6px 12px;
    border: none;
    color: white !important;
    font-weight: 500;
}

/* Excel - verde */
.dt-buttons .buttons-excel {
    background-color: #28a745 !important;
}
.dt-buttons .buttons-excel:hover {
    background-color: #218838 !important;
}

/* PDF - rojo */
.dt-buttons .buttons-pdf {
    background-color: #dc3545 !important;
}
.dt-buttons .buttons-pdf:hover {
    background-color: #c82333 !important;
}

/* Imprimir - gris */
.dt-buttons .buttons-print {
    background-color: #6c757d !important;
}
.dt-buttons .buttons-print:hover {
    background-color: #545b62 !important;
}

/* Tarjetas y tablas */
.card { border-radius: 12px; }
.table th { font-weight: 700; font-size: 0.95rem; }
</style>
@endpush

@section('js')
{{-- jsPDF --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

{{-- DataTables + Buttons --}}
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const agricultor = document.getElementById('agricultor');
    const parcela = document.getElementById('parcela');
    const plan = document.getElementById('plan');
    const btnMostrar = document.getElementById('btnMostrar');
    const btnPDF = document.getElementById('btnPDF');
    const detallesBody = document.getElementById('detallesBody');
    const areaPDF = document.getElementById('areaPDF');
    const sinDatos = document.getElementById('sinDatos');
    const tituloPlan = document.getElementById('tituloPlan');
    const impAgricultor = document.getElementById('impresionAgricultor');
    const impParcela = document.getElementById('impresionParcela');
    const impPlan = document.getElementById('impresionPlan');

    agricultor.selectedIndex = 0;
    resetSelect(parcela, 'Seleccione agricultor primero...');
    resetSelect(plan, 'Seleccione parcela primero...');

    agricultor.addEventListener('change', async () => {
        resetSelect(parcela, 'Cargando...');
        resetSelect(plan, 'Seleccione parcela primero...');
        btnMostrar.disabled = true;
        areaPDF.style.display = 'none';
        btnPDF.style.display = 'none';
        sinDatos.style.display = 'none';

        if (!agricultor.value) return;
        try {
            const res = await fetch(`/reportes/parcelas/${agricultor.value}`);
            const data = await res.json();
            if (data.length === 0) {
                resetSelect(parcela, 'Sin parcelas registradas');
                return;
            }

            parcela.innerHTML = '<option value="">Seleccione...</option>';
            data.forEach(p => parcela.innerHTML += `<option value="${p.id}">${p.nombre}</option>`);
            parcela.disabled = false;
        } catch {
            resetSelect(parcela, 'Error al cargar parcelas');
        }
    });

    parcela.addEventListener('change', async () => {
        resetSelect(plan, 'Cargando...');
        btnMostrar.disabled = true;
        areaPDF.style.display = 'none';
        btnPDF.style.display = 'none';
        sinDatos.style.display = 'none';
        if (!parcela.value) return;

        try {
            const res = await fetch(`/reportes/planes/${parcela.value}`);
            const data = await res.json();
            if (data.length === 0) {
                resetSelect(plan, 'Sin planes registrados');
                return;
            }

            plan.innerHTML = '<option value="">Seleccione...</option>';
            plan.innerHTML += '<option value="all">Ver todos los planes</option>';
            data.forEach(pl => plan.innerHTML += `<option value="${pl.id}">${pl.nombre} (${pl.anio_inicio})</option>`);
            plan.disabled = false;
        } catch {
            resetSelect(plan, 'Error al cargar planes');
        }
    });

    plan.addEventListener('change', () => {
        btnMostrar.disabled = !plan.value;
        areaPDF.style.display = 'none';
        sinDatos.style.display = 'none';
    });

    btnMostrar.addEventListener('click', async () => {
        if (!plan.value) return;
        detallesBody.innerHTML = `<tr><td colspan="6" class="text-muted">Cargando detalles...</td></tr>`;
        areaPDF.style.display = 'block';
        sinDatos.style.display = 'none';

        try {
            const nombreParcela = parcela.options[parcela.selectedIndex].text;
            let data = [];

            if (plan.value === "all") {
                const resPlanes = await fetch(`/reportes/planes/${parcela.value}`);
                const planes = await resPlanes.json();
                for (const pl of planes) {
                    const resDetalles = await fetch(`/reportes/detalles/${pl.id}`);
                    const detalles = await resDetalles.json();
                    data.push(...detalles.map(d => ({ ...d, parcela_nombre: nombreParcela, plan_nombre: pl.nombre })));
                }
                tituloPlan.textContent = "Todos los planes de la parcela seleccionada";
            } else {
                const res = await fetch(`/reportes/detalles/${plan.value}`);
                data = await res.json();
                data = data.map(d => ({ ...d, parcela_nombre: nombreParcela }));
                tituloPlan.textContent = plan.options[plan.selectedIndex].text;
            }

            if (!data || data.length === 0) {
                sinDatos.style.display = 'block';
                areaPDF.style.display = 'none';
                return;
            }

            detallesBody.innerHTML = data.map(d => {
                const img = d.es_descanso === 'Sí' ? '/images/descanso.png' : (d.cultivo?.imagen || '/images/no-image.png');
                const estado = renderEstado(d.ejecucion);
                return `
                    <tr>
                        <td>${d.parcela_nombre || '-'}</td>
                        <td>${d.anio}</td>
                        <td><img src="${img}" width="45" height="45" class="me-2 rounded border"> ${d.cultivo?.nombre || '—'}</td>
                        <td>${d.es_descanso}</td>
                        <td>${d.fechas}</td>
                        <td>${estado}</td>
                    </tr>
                `;
            }).join('');

            impAgricultor.textContent = agricultor.options[agricultor.selectedIndex].text;
            impParcela.textContent = parcela.options[parcela.selectedIndex].text;
            impPlan.textContent = plan.options[plan.selectedIndex].text;
            btnPDF.style.display = 'inline-block';

            // DataTable con exportación
            if ($.fn.DataTable.isDataTable('#tablaDatos')) {
                $('#tablaDatos').DataTable().destroy();
            }
            $('#tablaDatos').DataTable({
                pageLength: 10,
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm buttons-excel' },
                    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm buttons-pdf', orientation: 'landscape', pageSize: 'A4' },
                    { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary btn-sm buttons-print' }
                ],
                language: { url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
                order: [[1, 'asc']]
            });
        } catch (e) {
            console.error('Error al cargar detalles:', e);
            detallesBody.innerHTML = `<tr><td colspan="6" class="text-danger">Error al cargar datos.</td></tr>`;
        }
    });

    // Exportar PDF manual
    btnPDF.addEventListener('click', () => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');
        doc.setFontSize(16).setTextColor(40, 167, 69);
        doc.text("Reporte de Rotación por Agricultor", 420, 40, { align: "center" });
        doc.setFontSize(11).setTextColor(0, 0, 0);
        doc.text(`Agricultor: ${impAgricultor.textContent}`, 40, 70);
        doc.text(`Parcela: ${impParcela.textContent}`, 40, 90);
        doc.text(`Plan: ${impPlan.textContent}`, 40, 110);
        doc.autoTable({
            html: '#tablaDatos',
            startY: 130,
            theme: 'grid',
            styles: { halign: 'center', valign: 'middle', fontSize: 9 },
            headStyles: { fillColor: [40, 167, 69], textColor: 255 }
        });
        doc.save(`Rotacion_${impAgricultor.textContent.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.pdf`);
    });

    function resetSelect(select, msg) {
        select.innerHTML = `<option value="">${msg}</option>`;
        select.disabled = true;
    }

    function renderEstado(estado) {
        const clases = {
            'En ejecución': 'badge bg-success',
            'Planificado': 'badge bg-info',
            'Finalizado': 'badge bg-dark',
            'Pendiente': 'badge bg-warning',
        };
        return `<span class="${clases[estado] || 'badge bg-secondary'}">${estado}</span>`;
    }
});
</script>
@stop
