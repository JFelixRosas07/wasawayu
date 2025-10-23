<?php

namespace App\Http\Controllers;

use App\Models\Parcela;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParcelaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // si es agricultor, solo muestra sus parcelas
        if ($user->hasRole('Agricultor')) {
            $parcelas = Parcela::with('agricultor')
                ->where('agricultor_id', $user->id)
                ->paginate(10);
        } else {
            // admin y tecnicoagronomo pueden ver todas
            $parcelas = Parcela::with('agricultor')->paginate(10);
        }

        return view('parcelas.index', compact('parcelas'));
    }

    public function create()
    {
        $user = Auth::user();

        // agricultor no puede crear
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para crear parcelas.');
        }

        $agricultores = User::whereHas('roles', function ($q) {
            $q->where('name', 'Agricultor');
        })->pluck('name', 'id');

        return view('parcelas.create', compact('agricultores'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // agricultor no puede guardar
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para realizar esta accion.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'extension' => 'required|numeric|min:0',
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

        // agricultor solo puede ver sus propias parcelas
        if ($user->hasRole('Agricultor') && $parcela->agricultor_id !== $user->id) {
            abort(403, 'no puedes ver parcelas de otros agricultores.');
        }

        $parcela->load('agricultor');
        return view('parcelas.show', compact('parcela'));
    }

    public function edit(Parcela $parcela)
    {
        $user = Auth::user();

        // agricultor no puede editar
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para editar parcelas.');
        }

        $agricultores = User::whereHas('roles', function ($q) {
            $q->where('name', 'Agricultor');
        })->pluck('name', 'id');

        return view('parcelas.edit', compact('parcela', 'agricultores'));
    }

    public function update(Request $request, Parcela $parcela)
    {
        $user = Auth::user();

        // agricultor no puede actualizar
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para actualizar parcelas.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'extension' => 'required|numeric|min:0',
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

        // agricultor no puede eliminar
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para eliminar parcelas.');
        }

        // verificar si la parcela esta en uso en planes o detalles de rotacion
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
