@extends('adminlte::page')

@section('title', 'Planes de Rotación')

@section('content_header')
<h1><i class="fas fa-seedling"></i> Planes de Rotación</h1>
@stop

@section('content')
@php
    $parcelaId = request('parcela_id');
@endphp

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('admin.rotaciones.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>

            {{-- Mostrar botón "Nuevo Plan" solo si NO es Agricultor --}}
            @unless(auth()->user()->hasRole('Agricultor'))
                <a href="{{ route('planes.create', $parcelaId ? ['parcela_id' => $parcelaId] : []) }}"
                    class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Plan
                </a>
            @endunless
        </div>
    </div>

    <div class="card-body">
        {{-- Mostrar aviso si hay filtro activo --}}
        @if($parcelaId)
            <div class="alert alert-info py-2 mb-3">
                <small>
                    <i class="fas fa-filter"></i>
                    Mostrando planes filtrados por parcela.
                    <a href="{{ route('planes.index') }}" class="alert-link">Ver todos los planes</a>
                </small>
            </div>
        @endif

        <div class="table-responsive">
            <table id="planes-table" class="table table-bordered table-striped nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Plan de Rotación</th>
                        <th>Parcela</th>
                        <th>Agricultor</th>
                        <th>Ciclo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($planes as $plan)
                        @php
                            $nombreLimpio = preg_replace('/Rotación\s*/i', '', $plan->nombre);
                            $nombreLimpio = preg_replace('/\s*-\s*Ciclo\s*\d{4}\s*[-–]\s*\d{4}/i', '', $nombreLimpio);
                        @endphp
                        <tr>
                            <td>{{ trim($nombreLimpio) }}</td>
                            <td>{{ $plan->parcela->nombre ?? 'N/A' }}</td>
                            <td>{{ $plan->parcela->agricultor->name ?? 'N/A' }}</td>
                            <td>Ciclo {{ $plan->ciclo }}</td>
                            <td>
                                <span class="badge {{ $plan->badge_estado }}">
                                    {{ $plan->estado_texto }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                {{-- Todos pueden ver --}}
                                <a href="{{ route('planes.show', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}"
                                    class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- Solo Administrador y Técnico Agrónomo pueden editar o eliminar --}}
                                @unless(auth()->user()->hasRole('Agricultor'))
                                    <a href="{{ route('planes.edit', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}"
                                        class="btn btn-sm btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form
                                        action="{{ route('planes.destroy', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId]) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('¿Eliminar este plan?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endunless
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
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
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
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: -1 }
            ]
        });
    });
</script>
@stop
