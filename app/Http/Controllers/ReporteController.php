<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parcela;
use App\Models\Cultivo;
use App\Models\PlanRotacion;
use App\Models\DetalleRotacion;
use App\Models\EjecucionRotacion;
use App\Models\User;

class ReporteController extends Controller
{
    public function index()
    {
        $estadisticas = $this->obtenerEstadisticasGenerales();
        $datosGraficos = $this->obtenerDatosParaGraficos();
        
        return view('reportes.index', compact('estadisticas', 'datosGraficos'));
    }

    public function parcelas()
    {
        $parcelas = Parcela::with('agricultor')->get();
        $datosGraficos = $this->obtenerDatosParcelas();
        
        return view('reportes.parcelas', compact('parcelas', 'datosGraficos'));
    }

    public function cultivos()
    {
        $cultivos = Cultivo::all();
        $datosGraficos = $this->obtenerDatosCultivos();
        
        return view('reportes.cultivos', compact('cultivos', 'datosGraficos'));
    }

    public function rotaciones()
    {
        $rotaciones = PlanRotacion::with(['parcela', 'detalles.cultivo'])->get();
        $datosGraficos = $this->obtenerDatosRotaciones();
        
        return view('reportes.rotaciones', compact('rotaciones', 'datosGraficos'));
    }

    public function ejecuciones()
    {
        $ejecuciones = EjecucionRotacion::with(['detalle.plan.parcela', 'detalle.cultivo'])->get();
        $datosGraficos = $this->obtenerDatosEjecuciones();
        
        return view('reportes.ejecuciones', compact('ejecuciones', 'datosGraficos'));
    }

    public function generarPDF(Request $request)
    {
        $tipo = $request->get('tipo', 'general');
        
        // Lógica para generar PDF (puedes usar DomPDF o similar)
        return response()->json(['message' => 'PDF generado para: ' . $tipo]);
    }

    public function obtenerDatosGrafico(Request $request)
    {
        $tipo = $request->get('tipo');
        
        switch($tipo) {
            case 'cultivos_categoria':
                return response()->json($this->obtenerCultivosPorCategoria());
            case 'parcelas_suelo':
                return response()->json($this->obtenerParcelasPorTipoSuelo());
            case 'rotaciones_estado':
                return response()->json($this->obtenerRotacionesPorEstado());
            case 'ejecuciones_mensual':
                return response()->json($this->obtenerEjecucionesMensuales());
            default:
                return response()->json([]);
        }
    }

    private function obtenerEstadisticasGenerales()
    {
        return [
            'total_parcelas' => Parcela::count(),
            'total_cultivos' => Cultivo::count(),
            'total_rotaciones' => PlanRotacion::count(),
            'total_ejecuciones' => EjecucionRotacion::count(),
            'superficie_total' => Parcela::sum('extension'),
            'agricultores_activos' => User::role('Agricultor')->where('estado', 1)->count(),
            'rotaciones_ejecutandose' => PlanRotacion::where('estado', 'en_ejecucion')->count(),
            'cultivos_mas_usados' => $this->obtenerCultivosMasUsados()
        ];
    }

    private function obtenerDatosParaGraficos()
    {
        return [
            'cultivos_categoria' => $this->obtenerCultivosPorCategoria(),
            'parcelas_suelo' => $this->obtenerParcelasPorTipoSuelo(),
            'rotaciones_estado' => $this->obtenerRotacionesPorEstado(),
            'ejecuciones_mensual' => $this->obtenerEjecucionesMensuales()
        ];
    }

    private function obtenerCultivosPorCategoria()
    {
        return Cultivo::selectRaw('categoria, COUNT(*) as total')
            ->groupBy('categoria')
            ->get()
            ->pluck('total', 'categoria')
            ->toArray();
    }

    private function obtenerParcelasPorTipoSuelo()
    {
        return Parcela::selectRaw('tipoSuelo, COUNT(*) as total')
            ->groupBy('tipoSuelo')
            ->get()
            ->pluck('total', 'tipoSuelo')
            ->toArray();
    }

    private function obtenerRotacionesPorEstado()
    {
        return PlanRotacion::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->get()
            ->pluck('total', 'estado')
            ->toArray();
    }

    private function obtenerEjecucionesMensuales()
    {
        return EjecucionRotacion::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->whereYear('created_at', date('Y'))
            ->groupBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->toArray();
    }

    private function obtenerCultivosMasUsados($limite = 5)
    {
        return DetalleRotacion::whereNotNull('cultivo_id')
            ->selectRaw('cultivo_id, COUNT(*) as total')
            ->groupBy('cultivo_id')
            ->with('cultivo')
            ->orderByDesc('total')
            ->limit($limite)
            ->get()
            ->map(function($item) {
                return [
                    'nombre' => $item->cultivo->nombre,
                    'total' => $item->total
                ];
            });
    }

    private function obtenerDatosParcelas()
    {
        return [
            'por_tipo_suelo' => $this->obtenerParcelasPorTipoSuelo(),
            'por_uso_suelo' => Parcela::selectRaw('usoSuelo, COUNT(*) as total')
                ->groupBy('usoSuelo')
                ->get()
                ->pluck('total', 'usoSuelo')
                ->toArray(),
            'extension_promedio' => Parcela::avg('extension')
        ];
    }

    private function obtenerDatosCultivos()
    {
        return [
            'por_categoria' => $this->obtenerCultivosPorCategoria(),
            'por_carga_suelo' => Cultivo::selectRaw('cargaSuelo, COUNT(*) as total')
                ->groupBy('cargaSuelo')
                ->get()
                ->pluck('total', 'cargaSuelo')
                ->toArray(),
            'duracion_promedio' => Cultivo::avg('diasCultivo')
        ];
    }

    private function obtenerDatosRotaciones()
    {
        return [
            'por_estado' => $this->obtenerRotacionesPorEstado(),
            'por_anios' => PlanRotacion::selectRaw('anios, COUNT(*) as total')
                ->groupBy('anios')
                ->get()
                ->pluck('total', 'anios')
                ->toArray()
        ];
    }

    private function obtenerDatosEjecuciones()
    {
        return [
            'por_estado' => EjecucionRotacion::selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->get()
                ->pluck('total', 'estado')
                ->toArray(),
            'mensual' => $this->obtenerEjecucionesMensuales()
        ];
    }
}