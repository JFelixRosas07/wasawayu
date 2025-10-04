@extends('adminlte::page')

@section('title', 'Reportes - Wasawayu')

@section('content_header')
    <h1><i class="fas fa-chart-bar"></i> Reportes y Estadísticas</h1>
@stop

@section('content')
<div class="row">
    <!-- Estadísticas Rápidas -->
    <div class="col-12">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $estadisticas['total_parcelas'] }}</h3>
                        <p>Parcelas Registradas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <a href="{{ route('reportes.parcelas') }}" class="small-box-footer">
                        Más info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $estadisticas['total_cultivos'] }}</h3>
                        <p>Cultivos Registrados</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-seedling"></i>
                    </div>
                    <a href="{{ route('reportes.cultivos') }}" class="small-box-footer">
                        Más info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $estadisticas['total_rotaciones'] }}</h3>
                        <p>Planes de Rotación</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <a href="{{ route('reportes.rotaciones') }}" class="small-box-footer">
                        Más info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $estadisticas['total_ejecuciones'] }}</h3>
                        <p>Ejecuciones Registradas</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-play-circle"></i>
                    </div>
                    <a href="{{ route('reportes.ejecuciones') }}" class="small-box-footer">
                        Más info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Gráfico de Cultivos por Categoría -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Cultivos por Categoría</h3>
            </div>
            <div class="card-body">
                <div id="graficoCultivosCategoria" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Parcelas por Tipo de Suelo -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Parcelas por Tipo de Suelo</h3>
            </div>
            <div class="card-body">
                <div id="graficoParcelasSuelo" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <!-- Gráfico de Rotaciones por Estado -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie"></i> Rotaciones por Estado</h3>
            </div>
            <div class="card-body">
                <div id="graficoRotacionesEstado" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Ejecuciones Mensuales -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line"></i> Ejecuciones Mensuales</h3>
            </div>
            <div class="card-body">
                <div id="graficoEjecucionesMensual" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Cultivos Más Usados -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-seedling"></i> Cultivos Más Utilizados en Rotaciones</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Cultivo</th>
                                <th>Veces Utilizado</th>
                                <th>Categoría</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estadisticas['cultivos_mas_usados'] as $index => $cultivo)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $cultivo['nombre'] }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $cultivo['total'] }} veces</span>
                                </td>
                                <td>
                                    @php
                                        $cultivoModel = App\Models\Cultivo::where('nombre', $cultivo['nombre'])->first();
                                    @endphp
                                    @if($cultivoModel)
                                        <span class="badge bg-info">{{ $cultivoModel->categoria }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<!-- Incluir ECharts -->
<script src="https://cdn.jsdelivr.net/npm/echarts@5.4.3/dist/echarts.min.js"></script>
<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar todos los gráficos
        inicializarGraficoCultivosCategoria();
        inicializarGraficoParcelasSuelo();
        inicializarGraficoRotacionesEstado();
        inicializarGraficoEjecucionesMensual();
    });

    function inicializarGraficoCultivosCategoria() {
        var chart = echarts.init(document.getElementById('graficoCultivosCategoria'));
        
        var datos = @json($datosGraficos['cultivos_categoria']);
        
        var option = {
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b}: {c} ({d}%)'
            },
            legend: {
                orient: 'vertical',
                left: 'left',
            },
            series: [
                {
                    name: 'Cultivos',
                    type: 'pie',
                    radius: '50%',
                    data: Object.keys(datos).map(function(key) {
                        return { value: datos[key], name: key };
                    }),
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        };
        
        chart.setOption(option);
    }

    function inicializarGraficoParcelasSuelo() {
        var chart = echarts.init(document.getElementById('graficoParcelasSuelo'));
        
        var datos = @json($datosGraficos['parcelas_suelo']);
        
        var option = {
            tooltip: {
                trigger: 'axis',
                axisPointer: {
                    type: 'shadow'
                }
            },
            xAxis: {
                type: 'category',
                data: Object.keys(datos)
            },
            yAxis: {
                type: 'value'
            },
            series: [
                {
                    data: Object.values(datos),
                    type: 'bar',
                    itemStyle: {
                        color: function(params) {
                            var colorList = ['#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de'];
                            return colorList[params.dataIndex % colorList.length];
                        }
                    }
                }
            ]
        };
        
        chart.setOption(option);
    }

    function inicializarGraficoRotacionesEstado() {
        var chart = echarts.init(document.getElementById('graficoRotacionesEstado'));
        
        var datos = @json($datosGraficos['rotaciones_estado']);
        
        var option = {
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b}: {c} ({d}%)'
            },
            series: [
                {
                    name: 'Rotaciones',
                    type: 'pie',
                    radius: ['40%', '70%'],
                    avoidLabelOverlap: false,
                    itemStyle: {
                        borderRadius: 10,
                        borderColor: '#fff',
                        borderWidth: 2
                    },
                    label: {
                        show: false,
                        position: 'center'
                    },
                    emphasis: {
                        label: {
                            show: true,
                            fontSize: '18',
                            fontWeight: 'bold'
                        }
                    },
                    labelLine: {
                        show: false
                    },
                    data: Object.keys(datos).map(function(key) {
                        return { value: datos[key], name: key.charAt(0).toUpperCase() + key.slice(1) };
                    })
                }
            ]
        };
        
        chart.setOption(option);
    }

    function inicializarGraficoEjecucionesMensual() {
        var chart = echarts.init(document.getElementById('graficoEjecucionesMensual'));
        
        var datos = @json($datosGraficos['ejecuciones_mensual']);
        
        // Convertir números de mes a nombres
        var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        var datosMensuales = [];
        
        for (var i = 1; i <= 12; i++) {
            datosMensuales.push(datos[i] || 0);
        }
        
        var option = {
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                data: meses
            },
            yAxis: {
                type: 'value'
            },
            series: [
                {
                    data: datosMensuales,
                    type: 'line',
                    smooth: true,
                    lineStyle: {
                        width: 3
                    },
                    areaStyle: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                            { offset: 0, color: 'rgba(58,77,233,0.8)' },
                            { offset: 1, color: 'rgba(58,77,233,0.1)' }
                        ])
                    }
                }
            ]
        };
        
        chart.setOption(option);
    }

    // Redimensionar gráficos cuando cambia el tamaño de la ventana
    window.addEventListener('resize', function() {
        echarts.getInstanceByDom(document.getElementById('graficoCultivosCategoria'))?.resize();
        echarts.getInstanceByDom(document.getElementById('graficoParcelasSuelo'))?.resize();
        echarts.getInstanceByDom(document.getElementById('graficoRotacionesEstado'))?.resize();
        echarts.getInstanceByDom(document.getElementById('graficoEjecucionesMensual'))?.resize();
    });
</script>
@stop