@extends('adminlte::page')

@section('title', 'Parcelas')

@section('content_header')
<h1><i class="fas fa-map-marked-alt"></i> Gestión de Parcelas</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">

            {{-- Roles corregidos --}}
            @if(auth()->user()->hasAnyRole(['Administrador', 'TecnicoAgronomo']))
                <a href="{{ route('parcelas.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Parcela
                </a>
            @endif

            <a href="{{ route('parcelas.mapa-general') }}" class="btn btn-success">
                <i class="fas fa-map"></i> Ver Mapa General
            </a>
        </div>
    </div>

    <div class="card-body">

        {{-- Mensajes de éxito y error --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="table-responsive">
            <table id="tabla-parcelas" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th><i class="fas fa-signature"></i> Código Nombre</th>
                        <th><i class="fas fa-user"></i> Agricultor</th>
                        <th><i class="fas fa-ruler-combined"></i> Superficie (ha)</th>
                        <th><i class="fas fa-map-marker-alt"></i> Descripción de la Ubicación</th>
                        <th><i class="fas fa-seedling"></i> Uso Actual</th>
                        <th><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parcelas as $parcela)
                        <tr>
                            <td>{{ $parcela->nombre }}</td>
                            <td>
                                @if(optional($parcela->agricultor)->foto && file_exists(public_path($parcela->agricultor->foto)))
                                    <img src="{{ asset($parcela->agricultor->foto) }}" class="img-circle mr-2" width="40"
                                        height="40" alt="Foto" style="object-fit: cover;">
                                @endif
                                {{ optional($parcela->agricultor)->name ?? 'No asignado' }}
                            </td>
                            <td>{{ rtrim(rtrim(number_format($parcela->extension, 2, '.', ''), '0'), '.') }}</td>
                            <td>{{ $parcela->ubicacion }}</td>
                            <td>
                                <span class="badge badge-info">{{ $parcela->usoSuelo }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    {{-- 🔹 Todos pueden ver --}}
                                    <a href="{{ route('parcelas.show', $parcela) }}" class="btn btn-sm btn-primary" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- 🔹 Roles con permisos --}}
                                    @if(auth()->user()->hasAnyRole(['Administrador', 'TecnicoAgronomo']))
                                        <a href="{{ route('parcelas.edit', $parcela) }}" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('parcelas.destroy', $parcela) }}" method="POST" class="form-delete d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

<style>
/* Evita el destello de contenido sin estilo (FOUC) */
#tabla-parcelas {
    visibility: hidden;
}
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function () {
        // Inicializar DataTables
        $('#tabla-parcelas').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: { columns: [0, 1, 2, 3, 4] }
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: { columns: [0, 1, 2, 3, 4] },
                    customize: function (doc) {
                        doc.defaultStyle.fontSize = 9;
                        doc.styles.tableHeader.fontSize = 10;
                        doc.content[1].table.widths = ['20%', '25%', '15%', '25%', '15%'];
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-secondary btn-sm',
                    exportOptions: { columns: [0, 1, 2, 3, 4] }
                }
            ],
            columnDefs: [
                { targets: [5], orderable: false, searchable: false }
            ]
        });

        // Mostrar tabla una vez cargada
        $('#tabla-parcelas').css('visibility', 'visible');

        // Confirmación elegante al eliminar
        $('#tabla-parcelas').on('submit', '.form-delete', function (e) {
            e.preventDefault();
            var form = this;
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir la eliminación de esta parcela!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1B4332',
                cancelButtonColor: '#C44536',
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@stop
