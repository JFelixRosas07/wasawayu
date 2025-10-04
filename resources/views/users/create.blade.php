@extends('adminlte::page')

@section('title', 'Crear usuario')

@section('content_header')
<h1><i class="fas fa-user-plus"></i> Crear usuario</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" novalidate>
            @csrf

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                    pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" title="Solo se permiten letras y espacios" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" class="form-control" required minlength="6" maxlength="20"
                    title="Mínimo 6 caracteres, máximo 20.">
            </div>

            <div class="form-group">
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="role" class="form-control" required>
                    <option value="">Seleccione un rol</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ old('role') == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Fotografía</label>
                <input type="file" name="foto" class="form-control-file" accept="image/*" required>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" name="estado" class="form-check-input" id="estado" value="1" checked>
                <label class="form-check-label" for="estado">Activo</label>
            </div>

            <button class="btn btn-success"><i class="fas fa-save"></i> Crear</button>
            <a href="{{ route('users.index') }}" class="btn btn-danger"><i class="fas fa-times"></i> Cancelar</a>
        </form>
    </div>
</div>
@stop