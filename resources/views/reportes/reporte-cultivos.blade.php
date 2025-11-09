@extends('adminlte::page')

@section('title', 'Reporte de Cultivos')

@section('content_header')
<h1 class="text-success fw-bold display-6">
    <i class="fas fa-seedling me-2"></i>Reporte de Cultivos Activos
</h1>
@stop

@section('content')
{{-- Botones principales --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('reportes.index') }}" class="btn btn-success btn-lg">
        <i class="fas fa-arrow-left me-2"></i> Volver al Dashboard
    </a>
    <button id="btnPDF" class="btn btn-success btn-lg" disabled>
        <i class="fas fa-file-pdf me-2"></i> Exportar PDF
    </button>
</div>

{{-- Tarjeta del gráfico --}}
<div class="card shadow-lg border-0 mb-4">
    <div class="card-header bg-success text-white fw-bold py-3">
        <i class="fas fa-chart-pie me-2"></i>Distribución de Cultivos Activos
    </div>
    <div class="card-body">
        <div id="graficoCultivos" style="height: 400px;"></div>
        <p class="text-muted mt-3 text-center">
            Representación porcentual de los cultivos utilizados en el sistema agrícola.
        </p>
    </div>
</div>

{{-- Tarjeta de la tabla --}}
<div class="card border-success shadow-sm" id="contenedorTabla" style="display:none;">
    <div class="card-header bg-light border-success py-3">
        <h5 class="mb-0 text-success fw-bold">
            <i class="fas fa-table me-2"></i>Detalle de Cultivos
            <span class="badge bg-success text-white ms-2" id="contadorResultados">0 registros</span>
        </h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tablaCultivos" class="table table-striped table-bordered table-hover align-middle mb-0">
                <thead class="bg-success text-white">
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Variedad</th>
                        <th>Carga Suelo</th>
                        <th>Época Siembra</th>
                        <th>Época Cosecha</th>
                        <th>Cantidad de Usos</th>
                        <th>% de Participación</th>
                    </tr>
                </thead>
                <tbody id="cultivosBody"></tbody>
            </table>
        </div>
    </div>
</div>
@stop

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
{{-- Librerías principales --}}
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
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
document.addEventListener('DOMContentLoaded', async function () {
    const cultivosBody = document.getElementById('cultivosBody');
    const contenedorTabla = document.getElementById('contenedorTabla');
    const btnPDF = document.getElementById('btnPDF');
    const graficoCultivos = echarts.init(document.getElementById('graficoCultivos'));

    try {
        const res = await fetch(`{{ route('reportes.cultivos.data') }}`);
        const data = await res.json();

        if (!data || data.length === 0) {
            cultivosBody.innerHTML = `<tr><td colspan="8" class="text-muted">No hay cultivos registrados.</td></tr>`;
            graficoCultivos.setOption({ title: { text: 'Sin datos disponibles', left: 'center' } });
            return;
        }

        // === Llenar tabla ===
        cultivosBody.innerHTML = data.map(c => `
            <tr>
                <td><strong class="text-success">${c.nombre}</strong></td>
                <td>${c.categoria}</td>
                <td>${c.variedad}</td>
                <td>${c.cargaSuelo}</td>
                <td>${c.epocaSiembra}</td>
                <td>${c.epocaCosecha}</td>
                <td class="fw-bold">${c.cantidad}</td>
                <td class="fw-bold text-primary">${c.porcentaje}%</td>
            </tr>
        `).join('');

        contenedorTabla.style.display = 'block';
        btnPDF.disabled = false;
        document.getElementById('contadorResultados').textContent = `${data.length} registros`;

        // === Inicializar DataTable ===
        if ($.fn.DataTable.isDataTable('#tablaCultivos')) {
            $('#tablaCultivos').DataTable().destroy();
        }

        $('#tablaCultivos').DataTable({
            pageLength: 10,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm buttons-excel' },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm buttons-pdf', orientation: 'landscape', pageSize: 'A4' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary btn-sm buttons-print' }
            ],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
            order: [[7, 'desc']]
        });

        // === Gráfico de torta ===
        const graficoData = data.map(c => ({ name: c.nombre, value: c.porcentaje }));

        graficoCultivos.setOption({
            tooltip: { trigger: 'item' },
            legend: { bottom: 10 },
            series: [{
                name: 'Participación (%)',
                type: 'pie',
                radius: ['35%', '70%'],
                data: graficoData,
                label: { formatter: '{b}: {d}%' },
                itemStyle: { borderRadius: 8, borderColor: '#fff', borderWidth: 2 }
            }]
        });

    } catch (err) {
        console.error(err);
        cultivosBody.innerHTML = `<tr><td colspan="8" class="text-danger">Error al cargar cultivos.</td></tr>`;
    }

    // === Exportar PDF ===
    btnPDF.addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');

        doc.setFontSize(16).setTextColor(40, 167, 69);
        doc.text("Distribución de Cultivos Activos del Sistema", 420, 40, { align: "center" });
        doc.setFontSize(11).setTextColor(0, 0, 0);
        doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 40, 65);

        doc.autoTable({
            html: '#tablaCultivos',
            startY: 90,
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

        doc.save(`Distribucion_Cultivos_${new Date().toISOString().split('T')[0]}.pdf`);
    });
});
</script>
@stop
