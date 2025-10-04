<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\User;
use Illuminate\Http\Request;

class ParcelaController extends Controller
{
    public function index()
    {
        // Traemos la relación agricultor para evitar N+1
        $parcelas = Parcela::with('agricultor')->paginate(10);

        // Vista: resources/views/parcelas/index.blade.php
        return view('parcelas.index', compact('parcelas'));
    }

    public function create()
    {
        // Obtenemos solo usuarios con rol 'Agricultor' (Spatie)
        $agricultores = User::whereHas('roles', function ($q) {
            $q->where('name', 'Agricultor');
        })->pluck('name', 'id');

        // Vista: resources/views/parcelas/create.blade.php
        return view('parcelas.create', compact('agricultores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'extension' => 'required|numeric|min:0',
            'ubicacion' => 'required|string|max:255',
            'tipoSuelo' => 'required|string|max:255',
            'usoSuelo' => 'required|string|max:255',
            'poligono' => 'required|json',
            'agricultor_id' => 'required|exists:users,id',
        ]);

        // Convertimos la cadena JSON a array para que el cast en el modelo lo convierta a JSON al guardar
        $poligonoArray = json_decode($request->input('poligono'), true);

        $data = $request->only([
            'nombre',
            'extension',
            'ubicacion',
            'tipoSuelo',
            'usoSuelo',
            'agricultor_id'
        ]);

        $data['poligono'] = $poligonoArray;

        Parcela::create($data);

        return redirect()->route('parcelas.index')
            ->with('success', 'Parcela registrada correctamente.');
    }

    public function show(Parcela $parcela)
    {
        $parcela->load('agricultor');

        // Vista: resources/views/parcelas/show.blade.php
        return view('parcelas.show', compact('parcela'));
    }

    public function edit(Parcela $parcela)
    {
        $agricultores = User::whereHas('roles', function ($q) {
            $q->where('name', 'Agricultor');
        })->pluck('name', 'id');

        // Vista: resources/views/parcelas/edit.blade.php
        return view('parcelas.edit', compact('parcela', 'agricultores'));
    }

    public function update(Request $request, Parcela $parcela)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'extension' => 'required|numeric|min:0',
            'ubicacion' => 'required|string|max:255',
            'tipoSuelo' => 'required|string|max:255',
            'usoSuelo' => 'required|string|max:255',
            'poligono' => 'required|json',
            'agricultor_id' => 'required|exists:users,id',
        ]);

        $poligonoArray = json_decode($request->input('poligono'), true);

        $data = $request->only([
            'nombre',
            'extension',
            'ubicacion',
            'tipoSuelo',
            'usoSuelo',
            'agricultor_id'
        ]);

        $data['poligono'] = $poligonoArray;

        $parcela->update($data);

        return redirect()->route('parcelas.index')
            ->with('success', 'Parcela actualizada correctamente.');
    }

    public function destroy(Parcela $parcela)
    {
        $parcela->delete();

        return redirect()->route('parcelas.index')
            ->with('success', 'Parcela eliminada correctamente.');
    }

    public function mapaGeneral()
    {
        // Traemos todas las parcelas con sus agricultores y polígonos
        $parcelas = Parcela::with('agricultor')->get();

        // Vista: resources/views/parcelas/mapa-general.blade.php
        return view('parcelas.mapa-general', compact('parcelas'));
    }
}
