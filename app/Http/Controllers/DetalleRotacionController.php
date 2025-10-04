<?php

namespace App\Http\Controllers;

use App\Models\DetalleRotacion;
use App\Models\PlanRotacion;
use App\Models\Cultivo;
use Illuminate\Http\Request;

class DetalleRotacionController extends Controller
{
    public function create($plan_id)  // ✅ CORREGIDO: Cambiar parámetro
    {
        $plan = PlanRotacion::findOrFail($plan_id);  // ✅ Buscar manualmente
        $cultivos = Cultivo::all();
        return view('rotaciones.detalles.create', compact('plan','cultivos'));
    }

    public function store(Request $request, $plan_id)  // ✅ CORREGIDO: Cambiar parámetro
    {
        $plan = PlanRotacion::findOrFail($plan_id);  // ✅ Buscar manualmente
        
        $request->validate([
            'anio' => 'required|integer|min:1',
            'cultivo_id' => 'nullable|exists:cultivos,id',
            'es_descanso' => 'boolean',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        DetalleRotacion::create([
            'plan_id' => $plan->id,  // ✅ Usar ID del plan encontrado
            'anio' => $request->anio,
            'cultivo_id' => $request->es_descanso ? null : $request->cultivo_id,
            'es_descanso' => $request->es_descanso ?? 0,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        // ✅ CORREGIDO: Pasar el ID
        return redirect()->route('planes.show', $plan->id)
            ->with('success', 'Detalle de rotación agregado.');
    }

    public function edit(DetalleRotacion $detalle)
    {
        $detalle->load('plan');
        $cultivos = Cultivo::all();
        return view('rotaciones.detalles.edit', compact('detalle', 'cultivos'));
    }

    public function update(Request $request, DetalleRotacion $detalle)
    {
        $request->validate([
            'anio' => 'required|integer|min:1',
            'cultivo_id' => 'nullable|exists:cultivos,id',
            'es_descanso' => 'boolean',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $detalle->update([
            'anio' => $request->anio,
            'cultivo_id' => $request->es_descanso ? null : $request->cultivo_id,
            'es_descanso' => $request->es_descanso ?? 0,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        // ✅ CORREGIDO: Pasar el ID del plan
        return redirect()->route('planes.show', $detalle->plan->id)
            ->with('success', 'Detalle de rotación actualizado.');
    }
}