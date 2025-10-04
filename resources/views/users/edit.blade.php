@extends('adminlte::page')

@section('title', 'Editar usuario')

@section('content_header')
    <h1>Editar usuario</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        <form action="{{ route('users.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-group">
                <label>Contraseña (dejar vacío para no cambiar)</label>
                <input type="password" name="password" class="form-control">
            </div>

            <div class="form-group">
                <label>Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="role" class="form-control" required>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ ($user->roles->pluck('name')->first() == $r) ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Fotografía</label>
                @if($user->foto)
                    <div class="mb-2">
                        <img src="{{ asset($user->foto) }}" alt="Foto actual" width="80" class="img-circle">
                    </div>
                @endif
                <input type="file" name="foto" class="form-control-file">
            </div>

            <div class="form-group form-check">
                <input type="checkbox" name="estado" class="form-check-input" id="estado" value="1" {{ $user->estado ? 'checked' : '' }}>
                <label class="form-check-label" for="estado">Activo</label>
            </div>

            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop
