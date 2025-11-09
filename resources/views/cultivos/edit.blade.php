@extends('adminlte::page')

@section('title', 'Editar Cultivo')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-edit"></i> Editar Cultivo</h1>
        <a href="{{ route('cultivos.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
<div class="card shadow">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @php
            // separar las épocas actuales (ejemplo: "julio - agosto")
            $siembra = explode(' - ', $cultivo->epocaSiembra);
            $cosecha = explode(' - ', $cultivo->epocaCosecha);
            $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        @endphp

        <form action="{{ route('cultivos.update', $cultivo) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label><i class="fas fa-leaf"></i> Nombre del Cultivo</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cultivo->nombre) }}" required>
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
                    <input type="number" name="diasCultivo" class="form-control" value="{{ old('diasCultivo', $cultivo->diasCultivo) }}" required>
                </div>

                <div class="form-group col-md-4">
                    <label><i class="fas fa-leaf"></i> Variedad</label>
                    <input type="text" name="variedad" class="form-control" value="{{ old('variedad', $cultivo->variedad) }}">
                </div>
            </div>

            {{-- Epocas --}}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label><i class="fas fa-seedling"></i> Época de Siembra</label>
                    <div class="d-flex align-items-center">
                        <select name="siembra_inicio" class="form-control mr-2" required>
                            <option value="">Mes inicio</option>
                            @foreach($meses as $mes)
                                <option value="{{ $mes }}" {{ (isset($siembra[0]) && $siembra[0] == $mes) ? 'selected' : '' }}>
                                    {{ ucfirst($mes) }}
                                </option>
                            @endforeach
                        </select>
                        <span class="mx-1"> - </span>
                        <select name="siembra_fin" class="form-control ml-2" required>
                            <option value="">Mes fin</option>
                            @foreach($meses as $mes)
                                <option value="{{ $mes }}" {{ (isset($siembra[1]) && $siembra[1] == $mes) ? 'selected' : '' }}>
                                    {{ ucfirst($mes) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group col-md-6">
                    <label><i class="fas fa-tractor"></i> Época de Cosecha</label>
                    <div class="d-flex align-items-center">
                        <select name="cosecha_inicio" class="form-control mr-2" required>
                            <option value="">Mes inicio</option>
                            @foreach($meses as $mes)
                                <option value="{{ $mes }}" {{ (isset($cosecha[0]) && $cosecha[0] == $mes) ? 'selected' : '' }}>
                                    {{ ucfirst($mes) }}
                                </option>
                            @endforeach
                        </select>
                        <span class="mx-1"> - </span>
                        <select name="cosecha_fin" class="form-control ml-2" required>
                            <option value="">Mes fin</option>
                            @foreach($meses as $mes)
                                <option value="{{ $mes }}" {{ (isset($cosecha[1]) && $cosecha[1] == $mes) ? 'selected' : '' }}>
                                    {{ ucfirst($mes) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $cultivo->descripcion) }}</textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lightbulb"></i> Recomendaciones</label>
                <textarea name="recomendaciones" class="form-control" rows="3">{{ old('recomendaciones', $cultivo->recomendaciones) }}</textarea>
            </div>

            <div class="form-group">
                <label><i class="fas fa-image"></i> Imagen del Cultivo</label><br>
                @if($cultivo->imagen && file_exists(public_path($cultivo->imagen)))
                    <img src="{{ asset($cultivo->imagen) }}" class="img-thumbnail mb-2" style="width:120px; height:120px; object-fit:cover; border-radius:12px;">
                @endif
                <input type="file" name="imagen" class="form-control-file" accept="image/*">
                <small class="form-text text-muted">Si no selecciona una nueva, se mantendrá la actual.</small>
            </div>

            <div class="form-group mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Actualizar Cultivo
                </button>
                <a href="{{ route('cultivos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <a href="{{ route('cultivos.show', $cultivo) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Ver Detalles
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<style>
    .btn { margin-right: 5px; }
</style>
@stop
