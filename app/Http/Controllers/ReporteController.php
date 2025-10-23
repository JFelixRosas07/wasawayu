<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Parcela;
use App\Models\PlanRotacion;
use App\Models\DetalleRotacion;
use App\Models\EjecucionRotacion;
use App\Models\Cultivo;

class ReporteController extends Controller
{
    public function index()
    {
        $usuario = auth()->user();

        $parcelas = Parcela::when($usuario->hasRole('Agricultor'), fn($q) => $q->where('agricultor_id', $usuario->id));
        $cultivos = Cultivo::query();
        $planes = PlanRotacion::when($usuario->hasRole('Agricultor'), fn($q) => $q->whereHas('parcela', fn($s) => $s->where('agricultor_id', $usuario->id)));
        $ejecuciones = EjecucionRotacion::when($usuario->hasRole('Agricultor'), fn($q) => $q->whereHas('detalle.plan.parcela', fn($s) => $s->where('agricultor_id', $usuario->id)));

        $metricas = [
            'parcelas' => ['total_parcelas' => $parcelas->count()],
            'cultivos' => [
                'total_cultivos' => $cultivos->count(),
                'por_categoria' => $cultivos->selectRaw('categoria, COUNT(*) as total')->groupBy('categoria')->get(),
            ],
            'rotaciones' => [
                'total_planes' => $planes->count(),
                'por_estado' => collect([
                    ['estado' => 'Planificado', 'total' => $planes->where('anio_inicio', '>=', now()->year)->count()],
                    ['estado' => 'En ejecución', 'total' => $planes->where('anio_inicio', '<', now()->year)->count()],
                ]),
            ],
            'ejecuciones' => ['total_ejecuciones' => $ejecuciones->count()],
        ];

        return view('reportes.index', compact('metricas', 'usuario'));
    }

    public function parcelasAgricultor()
    {
        $usuario = Auth::user();
        $agricultores = $usuario->hasRole(['Administrador', 'TecnicoAgronomo'])
            ? User::role('Agricultor')->get()
            : collect([$usuario]);
        return view('reportes.reporte-parcelas-agricultor', compact('agricultores', 'usuario'));
    }

    public function rotacionAgricultor()
    {
        $usuario = Auth::user();
        $agricultores = $usuario->hasRole(['Administrador', 'TecnicoAgronomo'])
            ? User::role('Agricultor')->get()
            : collect([$usuario]);
        return view('reportes.reporte-rotacion-agricultor', compact('agricultores'));
    }

    public function cultivos()
    {
        return view('reportes.reporte-cultivos');
    }

    public function ejecuciones()
    {
        return view('reportes.reporte-ejecuciones');
    }

    public function parcelasData($id)
    {
        $usuario = Auth::user();

        if ($usuario->hasRole('Agricultor')) {
            if ($id !== (string) $usuario->id && $id !== 'todos') abort(403);
            $id = $usuario->id;
        }

        $parcelas = $id === 'todos'
            ? Parcela::with(['agricultor', 'planesRotacion'])->get()
            : Parcela::with(['agricultor', 'planesRotacion'])->where('agricultor_id', $id)->get();

        $resultado = $parcelas->map(function ($p) {
            $superficie = $p->extension !== null && $p->extension !== ''
                ? (is_numeric($p->extension) ? number_format((float) $p->extension, 2) : $p->extension)
                : '—';

            return [
                'id' => $p->id,
                'nombre' => $p->nombre ?? 'Sin nombre',
                'superficie' => $superficie,
                'ubicacion' => $p->ubicacion ?? '—',
                'tipo_suelo' => $p->tipoSuelo ?? '—',
                'estado' => $p->estado ?? 'Activa',
                'agricultor' => ['nombre' => $p->agricultor?->name ?? 'Sin asignar'],
                'planes' => $p->planesRotacion?->pluck('nombre') ?? [],
            ];
        });

        return response()->json($resultado);
    }

    public function planesData($id)
    {
        $usuario = Auth::user();
        $planes = PlanRotacion::where('parcela_id', $id)
            ->when($usuario->hasRole('Agricultor'), fn($q) => $q->whereHas('parcela', fn($s) => $s->where('agricultor_id', $usuario->id)))
            ->select('id', 'nombre', 'anio_inicio')
            ->get();
        return response()->json($planes);
    }

    public function detallesData($id)
    {
        $usuario = Auth::user();

        $detalles = DetalleRotacion::with(['cultivo', 'ejecuciones'])
            ->where('plan_id', $id)
            ->when($usuario->hasRole('Agricultor'), fn($q) => $q->whereHas('plan.parcela', fn($s) => $s->where('agricultor_id', $usuario->id)))
            ->get()
            ->map(function ($d) {
                if ($d->es_descanso) {
                    return [
                        'anio' => "Año {$d->anio}",
                        'cultivo' => ['nombre' => 'Descanso', 'imagen' => asset('images/descanso.png')],
                        'es_descanso' => 'Sí',
                        'fechas' => ($d->fecha_inicio && $d->fecha_fin)
                            ? $d->fecha_inicio->format('d/m/Y') . ' – ' . $d->fecha_fin->format('d/m/Y')
                            : '-',
                        'ejecucion' => 'Planificado',
                    ];
                }

                $imagen = $d->cultivo?->imagen ? asset($d->cultivo->imagen) : asset('images/cultivos/default.png');
                $estado = 'Pendiente';

                if ($d->ejecuciones->isNotEmpty()) {
                    $ultima = $d->ejecuciones->sortByDesc('fecha_siembra')->first();
                    $estado = match ($ultima->estado) {
                        'en_ejecucion' => 'En ejecución',
                        'planificado' => 'Planificado',
                        default => 'Finalizado',
                    };
                } else {
                    $hoy = now();
                    if ($hoy->between($d->fecha_inicio, $d->fecha_fin)) $estado = 'En ejecución';
                    elseif ($hoy->lt($d->fecha_inicio)) $estado = 'Planificado';
                }

                return [
                    'anio' => "Año {$d->anio}",
                    'cultivo' => ['nombre' => $d->cultivo?->nombre ?? '-', 'imagen' => $imagen],
                    'es_descanso' => 'No',
                    'fechas' => ($d->fecha_inicio && $d->fecha_fin)
                        ? $d->fecha_inicio->format('d/m/Y') . ' – ' . $d->fecha_fin->format('d/m/Y')
                        : '-',
                    'ejecucion' => $estado,
                ];
            });

        return response()->json($detalles);
    }

    public function cultivosData()
    {
        $cultivos = Cultivo::withCount(['detallesRotacion', 'ejecucionesRotacion'])->get();
        $totalUsos = max(1, $cultivos->sum(fn($c) => $c->detalles_rotacion_count + $c->ejecuciones_rotacion_count));

        $resultado = $cultivos->map(function ($c) use ($totalUsos) {
            $total = $c->detalles_rotacion_count + $c->ejecuciones_rotacion_count;
            $porcentaje = round(($total / $totalUsos) * 100, 2);

            return [
                'id' => $c->id,
                'nombre' => $c->nombre ?? '—',
                'categoria' => $c->categoria ?? '—',
                'variedad' => $c->variedad ?? '—',
                'cargaSuelo' => $c->cargaSuelo ?? '—',
                'epocaSiembra' => $c->epocaSiembra ?? '—',
                'epocaCosecha' => $c->epocaCosecha ?? '—',
                'cantidad' => $total,
                'porcentaje' => $porcentaje,
            ];
        });

        return response()->json($resultado);
    }

    public function ejecucionesData()
    {
        $usuario = auth()->user();

        $ejecuciones = EjecucionRotacion::with([
            'detalle.plan.parcela.agricultor',
            'cultivoReal',
            'detalle.cultivo'
        ])
            ->when($usuario->hasRole('Agricultor'), fn($q) => $q->whereHas('detalle.plan.parcela', fn($s) => $s->where('agricultor_id', $usuario->id)))
            ->get();

        $resultado = $ejecuciones->map(function ($e) {
            $fueExitoso = match ($e->fue_exitoso) {
                'si' => 'Exitoso',
                'no' => 'Fallido',
                'parcial' => 'Parcial',
                default => null
            };

            $rendimiento = ($e->cantidad_sembrada && $e->cantidad_cosechada && $e->cantidad_sembrada > 0)
                ? ($e->cantidad_cosechada / $e->cantidad_sembrada) * 100
                : null;

            return [
                'agricultor' => $e->detalle->plan->parcela->agricultor->name ?? '—',
                'parcela' => $e->detalle->plan->parcela->nombre ?? '—',
                'cultivo_plan' => $e->detalle->cultivo->nombre ?? '—',
                'cultivo_real' => $e->cultivoReal->nombre ?? '—',
                'variedad' => $e->cultivoReal->variedad ?? null,
                'fecha_siembra' => optional($e->fecha_siembra)->format('d/m/Y') ?? '—',
                'fecha_cosecha' => optional($e->fecha_cosecha)->format('d/m/Y') ?? '—',
                'cantidad_sembrada' => $e->cantidad_sembrada,
                'cantidad_cosechada' => $e->cantidad_cosechada,
                'unidad_medida' => $e->unidad_medida ?? '—',
                'area_cultivada' => $e->area_cultivada,
                'rendimiento_produccion' => $e->rendimiento_produccion,
                'rendimiento_parcela' => $e->rendimiento_parcela,
                'fue_exitoso' => $fueExitoso,
                'estado' => $e->estado ?? '—',
                'observaciones' => $e->observaciones ?? '—',
                'rendimiento_calculado' => $rendimiento
            ];
        });

        return response()->json($resultado);
    }
}
