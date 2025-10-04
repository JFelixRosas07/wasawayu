<?php

namespace App\Http\Controllers;

use App\Models\EjecucionRotacion;
use App\Models\DetalleRotacion;
use Illuminate\Http\Request;

class EjecucionRotacionController extends Controller
{
    public function create($detalle_id)
    {
        $detalle = DetalleRotacion::with('cultivo')->findOrFail($detalle_id);
        return view('rotaciones.ejecuciones.create', compact('detalle'));
    }

    public function store(Request $request, $detalle_id)
    {
        $request->validate([
            'fecha_siembra' => 'required|date',
            'fecha_cosecha' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        EjecucionRotacion::create([
            'detalle_id' => $detalle_id,
            'fecha_siembra' => $request->fecha_siembra,
            'fecha_cosecha' => $request->fecha_cosecha,
            'observaciones' => $request->observaciones,
            'estado' => 'en_proceso',
            'creado_por' => auth()->id(),
        ]);

        return redirect()->route('planes.show', DetalleRotacion::find($detalle_id)->plan_id)
                         ->with('success', 'Ejecución registrada.');
    }
}
