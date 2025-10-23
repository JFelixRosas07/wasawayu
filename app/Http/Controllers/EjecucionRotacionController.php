<?php

namespace App\Http\Controllers;

use App\Models\EjecucionRotacion;
use App\Models\DetalleRotacion;
use App\Models\Cultivo;
use Illuminate\Http\Request;

class EjecucionRotacionController extends Controller
{
    // mostrar formulario para registrar una nueva ejecucion
    public function create($detalle_id)
    {
        $detalle = DetalleRotacion::with('cultivo', 'plan')->findOrFail($detalle_id);
        $cultivos = Cultivo::orderBy('nombre')->get();

        return view('rotaciones.ejecuciones.create', compact('detalle', 'cultivos'));
    }

    // guardar una nueva ejecucion en la base de datos
    public function store(Request $request, $detalle_id)
    {
        $detalle = DetalleRotacion::findOrFail($detalle_id);

        $request->validate([
            'cultivo_real_id' => 'required|exists:cultivos,id',
            'fecha_siembra' => 'required|date',
            'fecha_cosecha' => 'nullable|date|after_or_equal:fecha_siembra',
            'cantidad_sembrada' => 'nullable|numeric|min:0',
            'cantidad_cosechada' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|in:cargas,arrobas,kg,qq,libras',
            'area_cultivada' => 'nullable|numeric|min:0',
            'fue_exitoso' => 'nullable|in:si,parcial,no',
            'observaciones' => 'nullable|string|max:5000',
        ]);

        // calcular rendimientos si hay datos suficientes
        $rendimiento_produccion = null;
        $rendimiento_parcela = null;

        if ($request->cantidad_sembrada > 0 && $request->cantidad_cosechada > 0) {
            $rendimiento_produccion = ($request->cantidad_cosechada / $request->cantidad_sembrada) * 100;
        }

        if ($request->cantidad_cosechada > 0 && $request->area_cultivada > 0) {
            $rendimiento_parcela = $request->cantidad_cosechada / $request->area_cultivada;
        }

        EjecucionRotacion::create([
            'detalle_id' => $detalle_id,
            'cultivo_real_id' => $request->cultivo_real_id,
            'fecha_siembra' => $request->fecha_siembra,
            'fecha_cosecha' => $request->fecha_cosecha,
            'cantidad_sembrada' => $request->cantidad_sembrada,
            'cantidad_cosechada' => $request->cantidad_cosechada,
            'unidad_medida' => $request->unidad_medida,
            'area_cultivada' => $request->area_cultivada,
            'fue_exitoso' => $request->fue_exitoso,
            'rendimiento_produccion' => $rendimiento_produccion,
            'rendimiento_parcela' => $rendimiento_parcela,
            'observaciones' => $request->observaciones,
            'estado' => 'en_proceso',
            'creado_por' => auth()->id(),
        ]);

        return redirect()
            ->route('planes.show', $detalle->plan_id)
            ->with('success', 'ejecucion registrada correctamente.');
    }

    // mostrar los datos de una ejecucion registrada
    public function show($id)
    {
        $ejecucion = EjecucionRotacion::with([
            'detalle.plan.parcela.agricultor',
            'detalle.cultivo',
            'cultivoReal'
        ])->findOrFail($id);

        return view('rotaciones.ejecuciones.show', compact('ejecucion'));
    }

    // mostrar formulario para editar una ejecucion existente
    public function edit($id)
    {
        $ejecucion = EjecucionRotacion::with([
            'detalle.plan',
            'detalle.cultivo',   // cargar cultivo del detalle
            'cultivoReal'
        ])->findOrFail($id);

        $cultivos = Cultivo::orderBy('nombre')->get();

        return view('rotaciones.ejecuciones.edit', compact('ejecucion', 'cultivos'));
    }

    // actualizar los datos de una ejecucion
    public function update(Request $request, $id)
    {
        $ejecucion = EjecucionRotacion::findOrFail($id);

        $request->validate([
            'cultivo_real_id' => 'required|exists:cultivos,id',
            'fecha_siembra' => 'required|date',
            'fecha_cosecha' => 'nullable|date|after_or_equal:fecha_siembra',
            'cantidad_sembrada' => 'nullable|numeric|min:0',
            'cantidad_cosechada' => 'nullable|numeric|min:0',
            'unidad_medida' => 'nullable|string|in:cargas,arrobas,kg,qq,libras',
            'area_cultivada' => 'nullable|numeric|min:0',
            'fue_exitoso' => 'nullable|in:si,parcial,no',
            'observaciones' => 'nullable|string|max:5000',
        ]);

        // recalcular rendimientos
        $rendimiento_produccion = null;
        $rendimiento_parcela = null;

        if ($request->cantidad_sembrada > 0 && $request->cantidad_cosechada > 0) {
            $rendimiento_produccion = ($request->cantidad_cosechada / $request->cantidad_sembrada) * 100;
        }

        if ($request->cantidad_cosechada > 0 && $request->area_cultivada > 0) {
            $rendimiento_parcela = $request->cantidad_cosechada / $request->area_cultivada;
        }

        $ejecucion->update([
            'cultivo_real_id' => $request->cultivo_real_id,
            'fecha_siembra' => $request->fecha_siembra,
            'fecha_cosecha' => $request->fecha_cosecha,
            'cantidad_sembrada' => $request->cantidad_sembrada,
            'cantidad_cosechada' => $request->cantidad_cosechada,
            'unidad_medida' => $request->unidad_medida,
            'area_cultivada' => $request->area_cultivada,
            'fue_exitoso' => $request->fue_exitoso,
            'rendimiento_produccion' => $rendimiento_produccion,
            'rendimiento_parcela' => $rendimiento_parcela,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()
            ->route('planes.show', $ejecucion->detalle->plan_id)
            ->with('success', 'ejecucion actualizada correctamente.');
    }
}
