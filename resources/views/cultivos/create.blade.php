@extends('adminlte::page')

@section('title', 'Nuevo Cultivo')

@section('content_header')
    <h1><i class="fas fa-plus-circle"></i> Registrar Cultivo</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('cultivos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label><i class="fas fa-leaf"></i> Nombre del Cultivo</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="form-group col-md-6">
                    <label><i class="fas fa-layer-group"></i> Categoría</label>
                    <select name="categoria" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="Cereal">Cereal</option>
                        <option value="Tubérculo">Tubérculo</option>
                        <option value="Leguminosa">Leguminosa</option>
                        <option value="Hortaliza">Hortaliza</option>
                        <option value="Frutal">Frutal</option>
                        <option value="Oleaginosa">Oleaginosa</option>
                        <option value="Forrajera">Forrajera</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label><i class="fas fa-seedling"></i> Carga de Suelo</label>
                    <select name="cargaSuelo" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="alta">Alta</option>
                        <option value="media">Media</option>
                        <option value="baja">Baja</option>
                        <option value="regenerativa">Regenerativa</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label><i class="fas fa-calendar-alt"></i> Días de Cultivo</label>
                    <input type="number" name="diasCultivo" class="form-control" min="1" required>
                </div>
                <div class="form-group col-md-4">
                    <label><i class="fas fa-leaf"></i> Variedad</label>
                    <input type="text" name="variedad" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label><i class="fas fa-seedling"></i> Época de Siembra</label>
                    <input type="text" name="epocaSiembra" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label><i class="fas fa-tractor"></i> Época de Cosecha</label>
                    <input type="text" name="epocaCosecha" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lightbulb"></i> Recomendaciones</label>
                <textarea name="recomendaciones" class="form-control" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-image"></i> Imagen del Cultivo</label>
                <input type="file" name="imagen" class="form-control-file" accept="image/*" required>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
            <a href="{{ route('cultivos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop
