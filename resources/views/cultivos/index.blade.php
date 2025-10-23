@extends('adminlte::page')

@section('title', 'Cultivos')

@section('content_header')
    <h1><i class="fas fa-seedling"></i> Gestión de Cultivos</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        {{-- Solo administradores y técnicos agrónomos pueden crear --}}
        @if(auth()->user()->hasAnyRole(['Administrador', 'TecnicoAgronomo']))
            <a href="{{ route('cultivos.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Cultivo
            </a>
        @endif
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

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="table-responsive">
            <table id="tabla-cultivos" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th><i class="fas fa-leaf"></i> Nombre</th>
                        <th><i class="fas fa-layer-group"></i> Categoría</th>
                        <th><i class="fas fa-seedling"></i> Carga de Suelo</th>
                        <th><i class="fas fa-image"></i> Imagen</th>
                        <th><i class="fas fa-cogs"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cultivos as $cultivo)
                        <tr>
                            <td>{{ $cultivo->nombre }}</td>
                            <td><span class="badge badge-info">{{ $cultivo->categoria }}</span></td>
                            <td>
                                @switch($cultivo->cargaSuelo)
                                    @case('alta') <span class="badge badge-danger">Alta</span> @break
                                    @case('media') <span class="badge badge-warning">Media</span> @break
                                    @case('baja') <span class="badge badge-success">Baja</span> @break
                                    @case('regenerativa') <span class="badge badge-primary">Regenerativa</span> @break
                                @endswitch
                            </td>
                            <td>
                                @if($cultivo->imagen && file_exists(public_path($cultivo->imagen)))
                                    <img src="{{ asset($cultivo->imagen) }}" 
                                         class="img-thumbnail" 
                                         style="width:100px; height:100px; object-fit:cover; border-radius:12px;">
                                @else
                                    <img src="{{ asset('images/default_cultivo.png') }}" 
                                         class="img-thumbnail" 
                                         style="width:100px; height:100px; object-fit:cover; border-radius:12px;">
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    {{-- Todos pueden ver --}}
                                    <a href="{{ route('cultivos.show', $cultivo) }}" class="btn btn-sm btn-primary" title="Ver Detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    {{-- Solo administradores y técnicos agrónomos pueden editar o eliminar --}}
                                    @if(auth()->user()->hasAnyRole(['Administrador', 'TecnicoAgronomo']))
                                        <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn btn-sm btn-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('cultivos.destroy', $cultivo) }}" method="POST" class="form-delete d-inline">
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
{{-- Estilos de DataTables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
@stop

@section('js')
{{-- Scripts de DataTables y extensiones --}}
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
$(document).ready(function() {
    // Inicializar DataTable
    $('#tabla-cultivos').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            emptyTable: "No hay datos disponibles en la tabla",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron coincidencias",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        },
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm', exportOptions: { columns: [0,1,2] } },
            { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm', exportOptions: { columns: [0,1,2] } },
            { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary btn-sm', exportOptions: { columns: [0,1,2] } }
        ],
        columnDefs: [
            { targets: [4], orderable: false, searchable: false } // Columna de acciones no ordenable ni buscable
        ]
    });

    // Confirmación de eliminación con SweetAlert2
    $('#tabla-cultivos').on('submit', '.form-delete', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Eliminar cultivo?',
            text: "No podrás revertir esta acción.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1B4332',
            cancelButtonColor: '#C44536',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) { form.submit(); }
        });
    });
});
</script>
@stop
