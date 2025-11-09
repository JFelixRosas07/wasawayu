<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ParcelaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('Agricultor')) {
            $parcelas = Parcela::with('agricultor')
                ->where('agricultor_id', $user->id)
                ->paginate(10);
        } else {
            $parcelas = Parcela::with('agricultor')->paginate(10);
        }

        return view('parcelas.index', compact('parcelas'));
    }

   public function create()
{
    $user = Auth::user();

    if ($user->hasRole('Agricultor')) {
        abort(403, 'no tienes permiso para crear parcelas.');
    }

    // Obtener agricultores con rol 'Agricultor'
    $agricultores = User::whereHas('roles', function ($q) {
        $q->where('name', 'Agricultor');
    })->pluck('name', 'id');

    // 🔹 Obtener todas las parcelas existentes con su agricultor
    $parcelas = Parcela::with('agricultor')->get();

    return view('parcelas.create', compact('agricultores', 'parcelas'));
}


    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para realizar esta accion.');
        }

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('parcelas')->where(function ($query) use ($request) {
                    return $query->where('agricultor_id', $request->agricultor_id);
                }),
            ],
            'extension' => 'required|numeric|min:0.001',
            'ubicacion' => 'required|string|max:255',
            'tipoSuelo' => 'required|string|max:255',
            'usoSuelo' => 'required|string|max:255',
            'poligono' => 'required|json',
            'agricultor_id' => 'required|exists:users,id',
        ]);

        $data = $request->only([
            'nombre',
            'extension',
            'ubicacion',
            'tipoSuelo',
            'usoSuelo',
            'agricultor_id'
        ]);

        $data['poligono'] = json_decode($request->poligono, true);

        Parcela::create($data);

        return redirect()->route('parcelas.index')
            ->with('success', 'parcela registrada correctamente.');
    }

    public function show(Parcela $parcela)
    {
        $user = Auth::user();

        if ($user->hasRole('Agricultor') && $parcela->agricultor_id !== $user->id) {
            abort(403, 'no puedes ver parcelas de otros agricultores.');
        }

        $parcela->load('agricultor');
        return view('parcelas.show', compact('parcela'));
    }

    public function edit(Parcela $parcela)
{
    $user = Auth::user();

    if ($user->hasRole('Agricultor')) {
        abort(403, 'no tienes permiso para editar parcelas.');
    }

    $agricultores = User::whereHas('roles', fn($q) => $q->where('name', 'Agricultor'))
        ->pluck('name', 'id');

    // 🔹 traer todas las parcelas registradas (para mostrar en el mapa)
    $parcelas = Parcela::with('agricultor')->get();

    return view('parcelas.edit', compact('parcela', 'agricultores', 'parcelas'));
}


    public function update(Request $request, Parcela $parcela)
    {
        $user = Auth::user();

        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para actualizar parcelas.');
        }

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('parcelas')
                    ->ignore($parcela->id)
                    ->where(function ($query) use ($request) {
                        return $query->where('agricultor_id', $request->agricultor_id);
                    }),
            ],
            'extension' => 'required|numeric|min:0.001',
            'ubicacion' => 'required|string|max:255',
            'tipoSuelo' => 'required|string|max:255',
            'usoSuelo' => 'required|string|max:255',
            'poligono' => 'required|json',
            'agricultor_id' => 'required|exists:users,id',
        ]);

        $data = $request->only([
            'nombre',
            'extension',
            'ubicacion',
            'tipoSuelo',
            'usoSuelo',
            'agricultor_id'
        ]);

        $data['poligono'] = json_decode($request->poligono, true);

        $parcela->update($data);

        return redirect()->route('parcelas.index')
            ->with('success', 'parcela actualizada correctamente.');
    }

    public function destroy(Parcela $parcela)
    {
        $user = Auth::user();

        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para eliminar parcelas.');
        }

        $tienePlanes = \App\Models\PlanRotacion::where('parcela_id', $parcela->id)->exists();

        $tieneDetalles = \App\Models\DetalleRotacion::whereHas('plan', function ($q) use ($parcela) {
            $q->where('parcela_id', $parcela->id);
        })->exists();

        if ($tienePlanes || $tieneDetalles) {
            return redirect()->route('parcelas.index')
                ->with('error', 'no se puede eliminar la parcela porque esta siendo utilizada en planes o detalles de rotacion.');
        }

        try {
            $parcela->delete();
            return redirect()->route('parcelas.index')
                ->with('success', 'parcela eliminada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('parcelas.index')
                ->with('error', 'error al eliminar la parcela: ' . $e->getMessage());
        }
    }

    public function mapaGeneral()
    {
        $parcelas = Parcela::with('agricultor')->get();

        return view('parcelas.mapa-general', compact('parcelas'));
    }
}
