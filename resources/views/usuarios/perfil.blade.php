@extends('adminlte::page')

@section('title', 'Mi Perfil')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-user"></i> Mi Perfil</h1>

    {{-- Botón de volver --}}
    @role('Administrador')
        <a href="{{ route('users.index') }}" class="btn btn-success">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    @else
        <a href="{{ route('home') }}" class="btn btn-success">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    @endrole
</div>
@stop

@section('content')
<div class="row">
    {{-- Columna izquierda: foto de perfil --}}
    <div class="col-md-4 d-flex">
        <div class="card card-primary card-outline w-100 h-100">
            <div class="card-body box-profile text-center">
                @if($usuario->foto)
                    <img class="profile-user-img img-fluid rounded" src="{{ asset($usuario->foto) }}" alt="Foto de usuario">
                @else
                    <img class="profile-user-img img-fluid rounded" src="{{ asset('images/default.png') }}" alt="Sin foto">
                @endif

                <h3 class="profile-username text-center mt-3">{{ $usuario->name }}</h3>
                <p class="text-muted text-center">
                    {{ $usuario->roles->pluck('name')->first() ?? 'Sin rol' }}
                </p>

                <span class="badge {{ $usuario->estado ? 'badge-success' : 'badge-danger' }}">
                    {{ $usuario->estado ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Columna derecha: información + formulario de edición --}}
    <div class="col-md-8 d-flex">
        <div class="card w-100 h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-id-card"></i> Información Personal</h3>
            </div>

            <div class="card-body">
                {{-- Mensajes --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Información fija del usuario --}}
                <ul class="list-group list-group-flush mb-4">
                    <li class="list-group-item">
                        <i class="fas fa-user text-success"></i> <strong>Nombre:</strong>
                        <span class="float-right">{{ $usuario->name }}</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-envelope text-success"></i> <strong>Correo:</strong>
                        <span class="float-right">{{ $usuario->email }}</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-user-tag text-success"></i> <strong>Rol:</strong>
                        <span class="float-right">{{ $usuario->roles->pluck('name')->first() ?? '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-toggle-on text-success"></i> <strong>Estado:</strong>
                        <span class="float-right">
                            @if($usuario->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-calendar-plus text-success"></i> <strong>Creado:</strong>
                        <span class="float-right">{{ $usuario->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                    <li class="list-group-item">
                        <i class="fas fa-calendar-check text-success"></i> <strong>Actualizado:</strong>
                        <span class="float-right">{{ $usuario->updated_at->format('d/m/Y H:i') }}</span>
                    </li>
                </ul>

                {{-- Formulario de actualización --}}
                <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="name">Editar Nombre</label>
                        <input type="text" name="name" id="name" class="form-control"
                            value="{{ old('name', $usuario->name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Editar Correo</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="{{ old('email', $usuario->email) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="foto">Cambiar Foto</label>
                        <input type="file" name="foto" id="foto" class="form-control">
                    </div>

                    <hr>
                    <h5>Cambiar Contraseña</h5>
                    <p class="text-muted mb-3">Déjalo vacío si no deseas cambiarla.</p>

                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="********">
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="********">
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@push('css')
<style>
    /* Imagen de perfil */
    .card-body .img-fluid {
        width: 250px;
        height: 250px;
        object-fit: cover;
        border: 5px solid var(--andino-hoja, #28a745);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 768px) {
        .card-body .img-fluid {
            width: 180px;
            height: 180px;
        }
    }

    .list-group-item {
        font-size: 1rem;
        padding: 12px 15px;
    }

    .list-group-item i {
        margin-right: 8px;
    }

    .alert {
        border-radius: 8px;
    }
</style>
@endpush
