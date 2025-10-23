{{-- resources/views/reportes/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Gestión de Reportes e Informes')

{{-- Cabecera principal --}}
@section('content_header')
<div class="p-4 mb-4 text-white text-center rounded shadow-sm"
    style="background: linear-gradient(135deg, #2e7d32 0%, #8bc34a 50%, #fdd835 100%);
           border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
    <h1 class="m-0 display-6 fw-bold"><i class="fas fa-chart-pie me-2"></i> Gestión de Reportes e Informes</h1>
    <p class="m-0 mt-2 fs-5 opacity-90">Monitoreo agrícola de la comunidad Wasa Wayu</p>
</div>
@stop

@section('content')
<div class="container-fluid">

    {{-- Tarjetas de métricas --}}
    <div class="row" id="contenedorMetricas">
        @php
            $cards = [
                ['color'=>'success','icon'=>'fa-map','titulo'=>'Parcelas Registradas','valor'=>$metricas['parcelas']['total_parcelas'] ?? 0,'ruta'=>'reportes.parcelas.agricultor'],
                ['color'=>'primary','icon'=>'fa-seedling','titulo'=>'Distribución de Cultivos','valor'=>$metricas['cultivos']['total_cultivos'] ?? 0,'ruta'=>'reportes.cultivos.sistema'],
                ['color'=>'info','icon'=>'fa-sync','titulo'=>'Planes de Rotación','valor'=>$metricas['rotaciones']['total_planes'] ?? 0,'ruta'=>'reportes.rotacion.agricultor'],
                ['color'=>'warning','icon'=>'fa-tractor','titulo'=>'Ejecuciones Realizadas','valor'=>$metricas['ejecuciones']['total_ejecuciones'] ?? 0,'ruta'=>'reportes.ejecuciones.sistema']
            ];
        @endphp

        @foreach($cards as $c)
        <div class="col-12 col-sm-6 col-lg-3 mb-4">
            <div class="card border-0 shadow-sm h-100 metric-card" style="border-left: 6px solid var(--bs-{{ $c['color'] }});">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="text-{{ $c['color'] }} display-6 fw-bold mb-1">{{ $c['valor'] }}</h2>
                            <p class="text-muted mb-2 fs-5 fw-medium">{{ $c['titulo'] }}</p>
                        </div>
                        <div class="bg-{{ $c['color'] }} rounded-circle p-3 ms-3 shadow-sm">
                            <i class="fas {{ $c['icon'] }} text-white fa-2x"></i>
                        </div>
                    </div>
                    <a href="{{ route($c['ruta']) }}" class="btn btn-lg btn-outline-{{ $c['color'] }} mt-3 w-100 py-2">
                        <i class="fas fa-eye me-2"></i> Ver Detalle
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

</div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Animación hover para las tarjetas
    document.querySelectorAll('.metric-card').forEach(card => {
        card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-8px)');
        card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
    });
});
</script>

<style>
/* Estilos para las tarjetas de métricas */
.metric-card {
    transition: all 0.3s ease;
    border-radius: 15px;
    min-height: 230px;
}

.metric-card:hover {
    box-shadow: 0 12px 35px rgba(0,0,0,0.25) !important;
}

/* Botones grandes de detalle */
.btn-lg {
    font-size: 1.05rem;
    font-weight: 500;
}
</style>
@stop
