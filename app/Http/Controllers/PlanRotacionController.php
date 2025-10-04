<?php

namespace App\Http\Controllers;

use App\Models\PlanRotacion;
use App\Models\Parcela;
use Illuminate\Http\Request;

class PlanRotacionController extends Controller
{
    public function index()
    {
        // Mostrar todos los planes con sus relaciones
        $planes = PlanRotacion::with('parcela.agricultor', 'creador')->get();
        return view('rotaciones.planes.index', compact('planes'));
    }

    public function create()
    {
        // Cargar parcelas con agricultor
        $parcelas = Parcela::with('agricultor')->get();
        return view('rotaciones.planes.create', compact('parcelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'parcela_id' => 'required|exists:parcelas,id',
            'nombre' => 'required|string|max:255',
            'anios' => 'required|integer|min:1|max:10',
        ]);

        $plan = PlanRotacion::create([
            'parcela_id' => $request->parcela_id,
            'nombre' => $request->nombre,
            'anios' => $request->anios,
            'creado_por' => auth()->id(),
            'estado' => 'planificado',
        ]);

        return redirect()->route('planes.show', $plan->id)->with('success', 'Plan de rotación creado.');
    }

    public function show($plan_id)
    {
        $plan = PlanRotacion::findOrFail($plan_id);
        $plan->load('detalles.cultivo', 'parcela.agricultor');
        return view('rotaciones.planes.show', compact('plan'));
    }

    // ✅ NUEVO MÉTODO: Vista visual de rotación
    public function visual($plan_id)
    {
        $plan = PlanRotacion::findOrFail($plan_id);
        $plan->load('detalles.cultivo', 'parcela.agricultor', 'detalles.ejecuciones');

        return view('rotaciones.planes.visual', compact('plan'));
    }

    public function edit($plan_id)
    {
        $plan = PlanRotacion::findOrFail($plan_id);
        $plan->load('parcela.agricultor');
        $parcelas = Parcela::with('agricultor')->get();
        return view('rotaciones.planes.edit', compact('plan', 'parcelas'));
    }

    public function update(Request $request, $plan_id)
    {
        $plan = PlanRotacion::findOrFail($plan_id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'anios' => 'required|integer|min:1|max:10',
        ]);

        $plan->update([
            'nombre' => $request->nombre,
            'anios' => $request->anios,
            'parcela_id' => $request->parcela_id,
        ]);

        return redirect()->route('planes.show', $plan->id)->with('success', 'Plan actualizado.');
    }

    public function destroy($plan_id)
    {
        $plan = PlanRotacion::findOrFail($plan_id);
        $plan->delete();
        return redirect()->route('planes.index')->with('success', 'Plan eliminado.');
    }
}