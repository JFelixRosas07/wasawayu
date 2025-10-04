@extends('adminlte::page')

@section('title', 'Perfil de Usuario')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-user"></i> Perfil de Usuario</h1>
    <a href="{{ route('users.index') }}" class="btn btn-success">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>
@stop

@section('content')
<div class="row">
    <div class="col-md-4 d-flex">
        <!-- Tarjeta con foto -->
        <div class="card card-primary card-outline w-100 h-100">
            <div class="card-body box-profile text-center">
                @if($user->foto)
                    <img class="profile-user-img img-fluid rounded" src="{{ asset($user->foto) }}" alt="Foto de usuario">
                @else
                    <img class="profile-user-img img-fluid rounded" src="{{ asset('images/default.png') }}" alt="Sin foto">
                @endif
                <h3 class="profile-username text-center mt-3">{{ $user->name }}</h3>
                <p class="text-muted text-center">
                    {{ $user->roles->pluck('name')->first() ?? 'Sin rol' }}
                </p>

                <span class="badge {{ $user->estado ? 'badge-success' : 'badge-danger' }}">
                    {{ $user->estado ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>

    <div class="col-md-8 d-flex">
        <!-- Detalles del usuario -->
        <div class="card w-100 h-100">
            <div class="card-header bg-success text-white">
                <h3 class="card-title"><i class="fas fa-id-card"></i> Información</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <i class="fas fa-user text-success"></i> <strong>Nombre:</strong>
                        <span class="float-right">{{ $user->name }}</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-envelope text-success"></i> <strong>Correo electrónico:</strong>
                        <span class="float-right">{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-user-tag text-success"></i> <strong>Rol:</strong>
                        <span class="float-right">{{ $user->roles->pluck('name')->first() ?? '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-toggle-on text-success"></i> <strong>Estado:</strong>
                        <span class="float-right">
                            @if($user->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-calendar-plus text-success"></i> <strong>Fecha de creación:</strong>
                        <span class="float-right">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-calendar-check text-success"></i> <strong>Última actualización:</strong>
                        <span class="float-right">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    /* Imagen de perfil más grande y cuadrada */
    .card-body .img-fluid {
        width: 250px;
        height: 250px;
        object-fit: cover;
        border: 5px solid var(--andino-hoja);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    @media (max-width: 768px) {
        .card-body .img-fluid {
            width: 180px;
            height: 180px;
        }
    }

    /* Estilo de lista de información */
    .list-group-item {
        font-size: 1rem;
        padding: 12px 15px;
    }
    .list-group-item i {
        margin-right: 8px;
    }
</style>
@endpush
