@extends('adminlte::page')

@section('title', 'Distribución de Cultivos Activos')

@section('content_header')
<h1 class="text-success">
    <i class="fas fa-seedling me-2"></i> Distribución de Cultivos Activos del Sistema
</h1>
@stop

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('reportes.index') }}" class="btn btn-success">
        <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
    </a>
    <button id="btnPDF" class="btn btn-success" disabled>
        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
    </button>
</div>

{{-- Distribución de Cultivos --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-success text-white fw-bold">
        <i class="fas fa-chart-pie me-2"></i> Distribución de Cultivos Activos
    </div>
    <div class="card-body">
        <div id="graficoCultivos" style="height: 400px;"></div>
        <p class="text-muted mt-3 text-center">
            Representación porcentual de los cultivos utilizados en el sistema agrícola.
        </p>
    </div>
</div>

{{-- Tabla de Cultivos --}}
<div id="tablaCultivos" class="mt-4" style="display:none;">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <strong>Participación de Cultivos</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center" id="tablaDatos">
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
</div>
@stop

@section('js')
{{-- Librerías principales --}}
<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>
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
document.addEventListener('DOMContentLoaded', async function () {
    const cultivosBody = document.getElementById('cultivosBody');
    const tablaCultivos = document.getElementById('tablaCultivos');
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

        // === LLENAR TABLA ===
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

        tablaCultivos.style.display = 'block';
        btnPDF.disabled = false;

        // === INICIALIZAR DATATABLE ===
        if ($.fn.DataTable.isDataTable('#tablaDatos')) {
            $('#tablaDatos').DataTable().destroy();
        }

        $('#tablaDatos').DataTable({
            pageLength: 10,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar', className: 'btn btn-success btn-sm' },
                { extend: 'csv', text: '<i class="fas fa-file-csv"></i> CSV', className: 'btn btn-success btn-sm' },
                { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-success btn-sm' },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-success btn-sm' }
            ],
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json" },
            order: [[7, 'desc']]
        });

        // === GRÁFICO DE DISTRIBUCIÓN ===
        const graficoData = data.map(c => ({
            name: c.nombre,
            value: c.porcentaje
        }));

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

    // === GENERAR PDF ===
    btnPDF.addEventListener('click', function () {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');

        doc.setFontSize(16).setTextColor(40, 167, 69);
        doc.text("Distribución de Cultivos Activos del Sistema", 420, 40, { align: "center" });
        doc.setFontSize(11).setTextColor(0, 0, 0);
        doc.text(`Fecha: ${new Date().toLocaleDateString()}`, 40, 65);

        doc.autoTable({
            html: '#tablaDatos',
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

<style>
.card { border-radius: 12px; }
.table th { font-weight: 700; font-size: 0.95rem; }
.badge { font-size: 0.8rem; }
.dt-buttons .btn {
    margin-right: 0.3rem;
    border-radius: 8px;
}
.dt-buttons .btn-success {
    background-color: #198754;
    border: none;
}
.dt-buttons .btn-success:hover {
    background-color: #157347;
}
</style>
@stop
