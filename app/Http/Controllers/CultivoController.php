<?php

namespace App\Http\Controllers;

use App\Models\Cultivo;
use App\Models\DetalleRotacion;
use App\Models\EjecucionRotacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CultivoController extends Controller
{
    // mostrar listado de cultivos
    public function index()
    {
        $cultivos = Cultivo::latest()->paginate(10);
        return view('cultivos.index', compact('cultivos'));
    }

    // mostrar formulario de creacion
    public function create()
    {
        $user = Auth::user();

        // agricultor no puede crear
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para crear cultivos.');
        }

        $categorias = ['Cereal', 'Tubérculo', 'Leguminosa', 'Hortaliza', 'Frutal'];
        $cargas = ['alta', 'media', 'baja', 'regenerativa'];

        return view('cultivos.create', compact('categorias', 'cargas'));
    }

    // guardar un nuevo cultivo
    public function store(Request $request)
    {
        $user = Auth::user();

        // agricultor no puede guardar
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para realizar esta accion.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'cargaSuelo' => 'required|string',
            'diasCultivo' => 'required|integer|min:1',
            'epocaSiembra' => 'required|string|max:255',
            'epocaCosecha' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'variedad' => 'nullable|string|max:255',
            'recomendaciones' => 'nullable|string',
            'imagen' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombre = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/cultivos'), $nombre);
            $data['imagen'] = 'images/cultivos/' . $nombre;
        }

        Cultivo::create($data);

        return redirect()->route('cultivos.index')
            ->with('success', 'cultivo registrado correctamente.');
    }

    // mostrar un cultivo
    public function show(Cultivo $cultivo)
    {
        return view('cultivos.show', compact('cultivo'));
    }

    // formulario para editar
    public function edit(Cultivo $cultivo)
    {
        $user = Auth::user();

        // agricultor no puede editar
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para editar cultivos.');
        }

        $categorias = ['Cereal', 'Tubérculo', 'Leguminosa', 'Hortaliza', 'Frutal'];
        $cargas = ['alta', 'media', 'baja', 'regenerativa'];

        return view('cultivos.edit', compact('cultivo', 'categorias', 'cargas'));
    }

    // actualizar cultivo
    public function update(Request $request, Cultivo $cultivo)
    {
        $user = Auth::user();

        // agricultor no puede actualizar
        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para actualizar cultivos.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'cargaSuelo' => 'required|string',
            'diasCultivo' => 'required|integer|min:1',
            'epocaSiembra' => 'required|string|max:255',
            'epocaCosecha' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'variedad' => 'nullable|string|max:255',
            'recomendaciones' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            // borrar imagen anterior si existe
            if ($cultivo->imagen && file_exists(public_path($cultivo->imagen))) {
                unlink(public_path($cultivo->imagen));
            }

            $file = $request->file('imagen');
            $nombre = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/cultivos'), $nombre);
            $data['imagen'] = 'images/cultivos/' . $nombre;
        }

        $cultivo->update($data);

        return redirect()->route('cultivos.index')
            ->with('success', 'cultivo actualizado correctamente.');
    }

    // eliminar cultivo
    public function destroy(Cultivo $cultivo)
    {
        $user = Auth::user();

        // solo administrador puede eliminar
        if (!$user->hasRole('Administrador')) {
            abort(403, 'no tienes permiso para eliminar cultivos.');
        }

        // verificar si el cultivo esta en uso en detalles o ejecuciones de rotacion
        $tieneDetalles = DetalleRotacion::where('cultivo_id', $cultivo->id)->exists();

        $tieneEjecuciones = EjecucionRotacion::whereHas('detalle', function ($q) use ($cultivo) {
            $q->where('cultivo_id', $cultivo->id);
        })->exists();

        if ($tieneDetalles || $tieneEjecuciones) {
            return redirect()->route('cultivos.index')
                ->with('error', 'no se puede eliminar el cultivo porque esta siendo utilizado en detalles o ejecuciones de rotacion.');
        }

        try {
            // eliminar imagen asociada si existe
            if ($cultivo->imagen && file_exists(public_path($cultivo->imagen))) {
                unlink(public_path($cultivo->imagen));
            }

            $cultivo->delete();

            return redirect()->route('cultivos.index')
                ->with('success', 'cultivo eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('cultivos.index')
                ->with('error', 'error al eliminar el cultivo: ' . $e->getMessage());
        }
    }
}
