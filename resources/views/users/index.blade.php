@extends('adminlte::page')

@section('title', 'Gestión de Usuarios')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-users me-2"></i>Gestión de Usuarios</h1>
    <a href="{{ route('users.create') }}" class="btn btn-success">
        <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
    </a>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-list me-2"></i>Lista de Usuarios Registrados
        </h5>
    </div>
    <div class="card-body">

        {{-- Alertas Mejoradas --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Error!</strong> Revise los siguientes campos:
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        {{-- Estadísticas Rápidas --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="info-box"
                    style="background: linear-gradient(135deg, var(--andino-oscuro) 0%, var(--andino-hoja) 100%); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Usuarios</span>
                        <span class="info-box-number">{{ $users->total() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="info-box"
                    style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Activos</span>
                        <span class="info-box-number">{{ $users->where('estado', true)->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="info-box"
                    style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <span class="info-box-icon"><i class="fas fa-user-tie"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Técnicos</span>
                        <span
                            class="info-box-number">{{ $users->filter(fn($u) => $u->roles->contains('name', 'TecnicoAgronomo'))->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="info-box"
                    style="background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                    <span class="info-box-icon"><i class="fas fa-tractor"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Agricultores</span>
                        <span
                            class="info-box-number">{{ $users->filter(fn($u) => $u->roles->contains('name', 'Agricultor'))->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tabla-usuarios" class="table table-hover table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th width="60"
                            style="background: linear-gradient(135deg, var(--andino-oscuro) 0%, var(--andino-hoja) 100%); color: white; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding: 1rem 0.75rem; vertical-align: middle;">
                            Foto</th>
                        <th
                            style="background: linear-gradient(135deg, var(--andino-oscuro) 0%, var(--andino-hoja) 100%); color: white; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding: 1rem 0.75rem; vertical-align: middle;">
                            Información</th>
                        <th
                            style="background: linear-gradient(135deg, var(--andino-oscuro) 0%, var(--andino-hoja) 100%); color: white; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding: 1rem 0.75rem; vertical-align: middle;">
                            Rol</th>
                        <th
                            style="background: linear-gradient(135deg, var(--andino-oscuro) 0%, var(--andino-hoja) 100%); color: white; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding: 1rem 0.75rem; vertical-align: middle;">
                            Estado</th>
                        <th width="180" class="text-center"
                            style="background: linear-gradient(135deg, var(--andino-oscuro) 0%, var(--andino-hoja) 100%); color: white; border: none; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; padding: 1rem 0.75rem; vertical-align: middle;">
                            Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        <tr class="{{ $u->estado ? '' : 'table-secondary' }}">
                            <td style="padding: 1rem 0.75rem; vertical-align: middle;">
                                <div
                                    style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; border: 3px solid #e9ecef; transition: all 0.3s ease; margin: 0 auto;">
                                    @if($u->foto && file_exists(public_path($u->foto)))
                                        <img src="{{ asset($u->foto) }}" style="width: 100%; height: 100%; object-fit: cover;"
                                            alt="{{ $u->name }}">
                                    @else
                                        <div
                                            style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--andino-oscuro) 0%, var(--andino-hoja) 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 1rem 0.75rem; vertical-align: middle;">
                                <div>
                                    <h6 class="mb-2 font-weight-bold text-dark">{{ $u->name }}</h6>
                                    <div style="line-height: 1.4;">
                                        <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                                            <i class="fas fa-envelope me-2 text-muted"
                                                style="width: 16px; text-align: center; font-size: 0.8rem;"></i>
                                            <span class="text-muted small">{{ $u->email }}</span>
                                        </div>
                                        <div style="display: flex; align-items: center;">
                                            <i class="fas fa-calendar me-2 text-muted"
                                                style="width: 16px; text-align: center; font-size: 0.8rem;"></i>
                                            <span class="text-muted small">Registro:
                                                {{ $u->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 0.75rem; vertical-align: middle;">
                                @php
                                    $roleName = $u->roles->pluck('name')->first() ?? '-';
                                    $badgeClass = match ($roleName) {
                                        'Administrador' => 'badge-danger',
                                        'TecnicoAgronomo' => 'badge-warning',
                                        'Agricultor' => 'badge-success',
                                        default => 'badge-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} badge-pill">{{ $roleName }}</span>
                            </td>
                            <td style="padding: 1rem 0.75rem; vertical-align: middle;">
                                <span class="badge {{ $u->estado ? 'badge-success' : 'badge-danger' }}">
                                    <i class="fas fa-circle me-1"></i>
                                    {{ $u->estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td style="padding: 1rem 0.75rem; vertical-align: middle;" class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('users.show', $u) }}" class="btn btn-info" data-toggle="tooltip"
                                        title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $u) }}" class="btn btn-warning" data-toggle="tooltip"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('users.toggle', $u) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $u->estado ? 'btn-danger' : 'btn-success' }}"
                                            data-toggle="tooltip" title="{{ $u->estado ? 'Desactivar' : 'Activar' }}"
                                            onclick="return confirm('¿Está seguro de {{ $u->estado ? 'desactivar' : 'activar' }} a {{ $u->name }}?')">
                                            @if($u->estado)
                                                <i class="fas fa-user-slash"></i>
                                            @else
                                                <i class="fas fa-user-check"></i>
                                            @endif
                                        </button>
                                    </form>
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
{{-- DataTables y estilos --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">

<style>
    /* Solo estilos específicos para esta página */
    .info-box {
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .info-box-icon {
        font-size: 1.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-group .btn {
        border-radius: 6px;
        margin: 0 2px;
    }

    .btn-group .btn:first-child {
        margin-left: 0;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .table tbody tr:hover {
        background: rgba(45, 106, 79, 0.05) !important;
    }

    /* Responsive para móviles */
    @media (max-width: 768px) {
        .table thead th {
            font-size: 0.7rem;
            padding: 0.75rem 0.5rem !important;
        }

        .table tbody td {
            padding: 0.75rem 0.5rem !important;
        }

        .avatar-wrapper {
            width: 40px;
            height: 40px;
        }
    }
</style>
@stop

@section('js')
{{-- DataTables y extensiones --}}
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

<script>
$(document).ready(function () {
    $('#tabla-usuarios').DataTable({
        responsive: true,
        autoWidth: false,
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }, // 👈 ESTA COMA ES CLAVE
        dom: '<"row"<"col-md-6"B><"col-md-6"f>>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: '<i class="fas fa-file-excel me-2"></i>Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: {
                    columns: [1, 2, 3]
                }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    columns: [1, 2, 3]
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print me-2"></i>Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    columns: [1, 2, 3]
                }
            }
        ],
        order: [[1, 'asc']],
        columnDefs: [
            { targets: [4], orderable: false, className: 'text-center' },
            { targets: [0], orderable: false }
        ]
    });

    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@stop
