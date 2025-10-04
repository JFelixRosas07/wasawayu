@extends('adminlte::page')

@section('title', 'Planes de Rotación')

@section('content_header')
    <h1>Planes de Rotación</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <a href="{{ route('planes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Plan
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="planes-table" class="table table-bordered table-striped nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Parcela</th>
                        <th>Agricultor</th>
                        <th>Años</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($planes as $plan)
                    <tr>
                        <td>{{ $plan->nombre }}</td>
                        <td>{{ $plan->parcela->nombre ?? 'N/A' }}</td>
                        <td>{{ $plan->parcela->agricultor->name ?? 'N/A' }}</td>
                        <td>{{ $plan->anios }}</td>
                        <td>
                            <span class="badge badge-info">
                                {{ ucfirst($plan->estado) }}
                            </span>
                        </td>
                        <td class="text-nowrap">
                            <a href="{{ route('planes.show', $plan) }}" 
                               class="btn btn-sm btn-info" 
                               title="Ver">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('planes.edit', $plan) }}" 
                               class="btn btn-sm btn-warning"
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('planes.destroy', $plan) }}" 
                                  method="POST" 
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="btn btn-sm btn-danger" 
                                        onclick="return confirm('¿Eliminar este plan?')"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
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
    {{-- DataTables Responsive CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
@stop

@section('js')
    {{-- DataTables Responsive JS --}}
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    
    <script>
    $(function () {
        $('#planes-table').DataTable({
            responsive: true,
            autoWidth: false,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json"
            },
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // Nombre siempre visible
                { responsivePriority: 2, targets: -1 }  // Acciones siempre visible
            ]
        });
    });
    </script>
@stop