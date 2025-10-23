<?php

namespace App\Http\Controllers;

use App\Models\PlanRotacion;
use App\Models\Parcela;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlanRotacionController extends Controller
{
    // mostrar listado de planes
    // si se pasa parcela_id, filtra solo los planes de esa parcela
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = PlanRotacion::with('parcela.agricultor', 'creador');

        // si el usuario es agricultor, solo sus propias parcelas
        if ($user->hasRole('Agricultor')) {
            $query->whereHas('parcela', function ($q) use ($user) {
                $q->where('agricultor_id', $user->id);
            });
        }

        // filtro por parcela desde el dashboard
        if ($request->filled('parcela_id')) {
            $query->where('parcela_id', $request->parcela_id);
        }

        // ordenar por mas recientes
        $planes = $query->orderByDesc('anio_inicio')->get();

        $parcelaId = $request->get('parcela_id');

        return view('rotaciones.planes.index', compact('planes', 'parcelaId'));
    }

    // formulario de creacion de plan
    public function create(Request $request)
    {
        $user = auth()->user();

        // si es agricultor, solo puede crear planes en sus parcelas
        if ($user->hasRole('Agricultor')) {
            $parcelas = Parcela::where('agricultor_id', $user->id)->with('agricultor')->get();
        } else {
            $parcelas = Parcela::with('agricultor')->get();
        }

        $parcelaId = $request->get('parcela_id');

        $inicioEstimadoPorParcela = [];
        $bloqueosPorParcela = [];

        foreach ($parcelas as $parcela) {
            $ultimoPlan = $parcela->planesRotacion()
                ->orderBy('anio_inicio', 'desc')
                ->first();

            if ($ultimoPlan && $ultimoPlan->anio_inicio !== null && $ultimoPlan->anio_inicio > 0) {
                $anioFinUltimoPlan = $ultimoPlan->anio_fin;

                if (
                    in_array($ultimoPlan->estado, ['en_ejecucion', 'planificado']) &&
                    now()->year <= $anioFinUltimoPlan
                ) {
                    $bloqueosPorParcela[$parcela->id] =
                        "plan {$ultimoPlan->estado}: {$ultimoPlan->nombre} ({$ultimoPlan->ciclo})";
                }

                $inicioEstimadoPorParcela[$parcela->id] = $anioFinUltimoPlan + 1;
            } else {
                $inicioEstimadoPorParcela[$parcela->id] = now()->year;

                if ($ultimoPlan && ($ultimoPlan->anio_inicio === null || $ultimoPlan->anio_inicio <= 0)) {
                    Log::warning("plan con anio_inicio invalido detectado", [
                        'plan_id' => $ultimoPlan->id,
                        'parcela_id' => $parcela->id,
                        'anio_inicio' => $ultimoPlan->anio_inicio
                    ]);
                }
            }
        }

        $anioInicioEstimado = $parcelaId
            ? ($inicioEstimadoPorParcela[$parcelaId] ?? now()->year)
            : now()->year;

        return view('rotaciones.planes.create', compact(
            'parcelas',
            'parcelaId',
            'inicioEstimadoPorParcela',
            'anioInicioEstimado',
            'bloqueosPorParcela'
        ));
    }

    // guardar nuevo plan
    public function store(Request $request)
    {
        $request->validate([
            'parcela_id' => 'required|exists:parcelas,id',
            'nombre' => 'required|string|max:255',
        ]);

        $parcela = Parcela::findOrFail($request->parcela_id);

        // agricultor no puede crear plan en otra parcela
        if (auth()->user()->hasRole('Agricultor') && $parcela->agricultor_id !== auth()->id()) {
            abort(403, 'no tienes permiso para crear planes en esta parcela.');
        }

        $ultimoPlan = $parcela->planesRotacion()
            ->orderBy('anio_inicio', 'desc')
            ->first();

        $anioInicio = now()->year;

        if ($ultimoPlan && $ultimoPlan->anio_inicio > 0) {
            $anioFinUltimoPlan = $ultimoPlan->anio_fin;
            $anioInicio = max($anioFinUltimoPlan + 1, now()->year);

            $ultimoAnioDetalles = $ultimoPlan->detalles()->max('anio');
            if ($ultimoAnioDetalles) {
                $anioInicio = max($anioInicio, $ultimoAnioDetalles + 1);
            }

            if (
                in_array($ultimoPlan->estado, ['en_ejecucion', 'planificado']) &&
                $anioInicio <= $anioFinUltimoPlan
            ) {
                return back()->withErrors([
                    'parcela_id' => "esta parcela ya tiene un plan {$ultimoPlan->estado} ({$ultimoPlan->nombre}) para el ciclo {$ultimoPlan->ciclo}."
                ])->withInput();
            }
        }

        $plan = PlanRotacion::create([
            'parcela_id' => $request->parcela_id,
            'nombre' => $request->nombre,
            'anio_inicio' => $anioInicio,
            'creado_por' => auth()->id(),
            'estado' => 'planificado',
        ]);

        return redirect()
            ->route('planes.index', ['parcela_id' => $request->parcela_id])
            ->with('success', "plan '{$plan->nombre}' creado correctamente para el ciclo {$plan->ciclo}.");
    }

    // mostrar un plan especifico con detalles y alertas
    public function show(Request $request, $plan_id)
    {
        $plan = PlanRotacion::with([
            'detalles.cultivo',
            'detalles.alertas',
            'parcela.agricultor'
        ])->findOrFail($plan_id);

        $user = auth()->user();

        if ($user->hasRole('Agricultor') && $plan->parcela->agricultor_id !== $user->id) {
            abort(403, 'no tienes permiso para ver este plan.');
        }

        $parcelaId = $request->get('parcela_id');

        return view('rotaciones.planes.show', compact('plan', 'parcelaId'));
    }

    // vista visual del plan (mapa o diagrama)
    public function visual($plan_id)
    {
        $plan = PlanRotacion::with([
            'detalles.cultivo',
            'parcela.agricultor',
            'detalles.ejecuciones'
        ])->findOrFail($plan_id);

        $user = auth()->user();

        if ($user->hasRole('Agricultor') && $plan->parcela->agricultor_id !== $user->id) {
            abort(403, 'no tienes permiso para ver este plan.');
        }

        return view('rotaciones.planes.visual', compact('plan'));
    }

    // formulario de edicion
    public function edit(Request $request, $plan_id)
    {
        $plan = PlanRotacion::with('parcela.agricultor')->findOrFail($plan_id);
        $parcelas = Parcela::with('agricultor')->get();
        $parcelaId = $request->get('parcela_id');

        return view('rotaciones.planes.edit', compact('plan', 'parcelas', 'parcelaId'));
    }

    // actualizar plan existente
    public function update(Request $request, $plan_id)
    {
        $plan = PlanRotacion::findOrFail($plan_id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'parcela_id' => 'required|exists:parcelas,id',
            'anio_inicio' => 'required|integer|min:2020|max:2030',
        ]);

        if ($plan->parcela_id != $request->parcela_id && $plan->detalles()->exists()) {
            return back()->withErrors([
                'parcela_id' => 'no se puede cambiar la parcela de un plan que ya tiene detalles registrados.'
            ])->withInput();
        }

        $plan->update([
            'nombre' => $request->nombre,
            'parcela_id' => $request->parcela_id,
            'anio_inicio' => $request->anio_inicio,
        ]);

        return redirect()
            ->route('planes.index', ['parcela_id' => $request->get('parcela_id')])
            ->with('success', 'plan de rotacion actualizado correctamente.');
    }

    // eliminar plan
    public function destroy(Request $request, $plan_id)
    {
        $plan = PlanRotacion::findOrFail($plan_id);
        $parcelaId = $request->get('parcela_id');

        if (!$plan->puedeEliminar()) {
            return back()->withErrors([
                'error' => 'no se puede eliminar este plan porque tiene ejecuciones registradas.'
            ]);
        }

        $nombrePlan = $plan->nombre;
        $plan->delete();

        return redirect()
            ->route('planes.index', ['parcela_id' => $parcelaId])
            ->with('success', "plan '{$nombrePlan}' eliminado correctamente.");
    }

    // cambiar estado de un plan manualmente
    public function cambiarEstado(Request $request, $plan_id)
    {
        $request->validate([
            'estado' => 'required|in:planificado,en_ejecucion,finalizado'
        ]);

        $plan = PlanRotacion::findOrFail($plan_id);
        $estadoAnterior = $plan->estado;

        $plan->update(['estado' => $request->estado]);

        Log::info("estado del plan cambiado manualmente", [
            'plan_id' => $plan->id,
            'nombre' => $plan->nombre,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $request->estado,
            'usuario' => auth()->user()->name
        ]);

        return back()->with('success', "estado del plan actualizado a '{$request->estado}'.");
    }
}
