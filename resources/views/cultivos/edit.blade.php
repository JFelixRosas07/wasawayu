@extends('adminlte::page')

@section('title', 'Editar Cultivo')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Editar Cultivo</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ route('cultivos.update', $cultivo) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label><i class="fas fa-leaf"></i> Nombre del Cultivo</label>
                    <input type="text" name="nombre" class="form-control" value="{{ $cultivo->nombre }}" required>
                </div>

                <div class="form-group col-md-6">
                    <label><i class="fas fa-layer-group"></i> Categoría</label>
                    <select name="categoria" class="form-control" required>
                        <option value="Cereal" {{ $cultivo->categoria == 'Cereal' ? 'selected' : '' }}>Cereal</option>
                        <option value="Tubérculo" {{ $cultivo->categoria == 'Tubérculo' ? 'selected' : '' }}>Tubérculo</option>
                        <option value="Leguminosa" {{ $cultivo->categoria == 'Leguminosa' ? 'selected' : '' }}>Leguminosa</option>
                        <option value="Hortaliza" {{ $cultivo->categoria == 'Hortaliza' ? 'selected' : '' }}>Hortaliza</option>
                        <option value="Frutal" {{ $cultivo->categoria == 'Frutal' ? 'selected' : '' }}>Frutal</option>
                        <option value="Oleaginosa" {{ $cultivo->categoria == 'Oleaginosa' ? 'selected' : '' }}>Oleaginosa</option>
                        <option value="Forrajera" {{ $cultivo->categoria == 'Forrajera' ? 'selected' : '' }}>Forrajera</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label><i class="fas fa-seedling"></i> Carga de Suelo</label>
                    <select name="cargaSuelo" class="form-control" required>
                        <option value="alta" {{ $cultivo->cargaSuelo == 'alta' ? 'selected' : '' }}>Alta</option>
                        <option value="media" {{ $cultivo->cargaSuelo == 'media' ? 'selected' : '' }}>Media</option>
                        <option value="baja" {{ $cultivo->cargaSuelo == 'baja' ? 'selected' : '' }}>Baja</option>
                        <option value="regenerativa" {{ $cultivo->cargaSuelo == 'regenerativa' ? 'selected' : '' }}>Regenerativa</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label><i class="fas fa-calendar-alt"></i> Días de Cultivo</label>
                    <input type="number" name="diasCultivo" class="form-control" value="{{ $cultivo->diasCultivo }}" required>
                </div>
                <div class="form-group col-md-4">
                    <label><i class="fas fa-leaf"></i> Variedad</label>
                    <input type="text" name="variedad" class="form-control" value="{{ $cultivo->variedad }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label><i class="fas fa-seedling"></i> Época de Siembra</label>
                    <input type="text" name="epocaSiembra" class="form-control" value="{{ $cultivo->epocaSiembra }}" required>
                </div>
                <div class="form-group col-md-6">
                    <label><i class="fas fa-tractor"></i> Época de Cosecha</label>
                    <input type="text" name="epocaCosecha" class="form-control" value="{{ $cultivo->epocaCosecha }}" required>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ $cultivo->descripcion }}</textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lightbulb"></i> Recomendaciones</label>
                <textarea name="recomendaciones" class="form-control" rows="3">{{ $cultivo->recomendaciones }}</textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-image"></i> Imagen del Cultivo</label><br>
                @if($cultivo->imagen && file_exists(public_path($cultivo->imagen)))
                    <img src="{{ asset($cultivo->imagen) }}" class="img-thumbnail mb-2" style="width:120px; height:120px; object-fit:cover; border-radius:12px;">
                @endif
                <input type="file" name="imagen" class="form-control-file" accept="image/*">
                <small class="form-text text-muted">Si no selecciona una nueva, se mantendrá la actual.</small>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Actualizar</button>
            <a href="{{ route('cultivos.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@stop
