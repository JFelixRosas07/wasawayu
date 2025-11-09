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

    // mostrar formulario de creación
    public function create()
    {
        $user = Auth::user();

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

        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para realizar esta acción.');
        }

        $meses = [
            'enero',
            'febrero',
            'marzo',
            'abril',
            'mayo',
            'junio',
            'julio',
            'agosto',
            'septiembre',
            'octubre',
            'noviembre',
            'diciembre'
        ];

        $request->validate([
            'nombre' => ['required', 'regex:/^[\pL\s]+$/u', 'max:255', 'unique:cultivos,nombre'],
            'categoria' => 'required|string|max:255',
            'cargaSuelo' => 'required|string',
            'diasCultivo' => 'required|integer|min:1|max:365',
            'siembra_inicio' => 'required|string|in:' . implode(',', $meses),
            'siembra_fin' => 'required|string|in:' . implode(',', $meses),
            'cosecha_inicio' => 'required|string|in:' . implode(',', $meses),
            'cosecha_fin' => 'required|string|in:' . implode(',', $meses),
            'descripcion' => 'nullable|string',
            'variedad' => ['nullable', 'regex:/^[\pL\s]+$/u', 'max:255'],
            'recomendaciones' => 'nullable|string',
            'imagen' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'nombre.unique' => 'Ya existe un cultivo con este nombre.',
            'nombre.regex' => 'El nombre del cultivo solo debe contener letras y espacios.',
            'variedad.regex' => 'La variedad solo debe contener letras y espacios.',
            'diasCultivo.max' => 'Los días de cultivo no pueden superar los 365 días.',
        ]);

        $data = $request->all();
        $data['epocaSiembra'] = "{$request->siembra_inicio} - {$request->siembra_fin}";
        $data['epocaCosecha'] = "{$request->cosecha_inicio} - {$request->cosecha_fin}";

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombre = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/cultivos'), $nombre);
            $data['imagen'] = 'images/cultivos/' . $nombre;
        }

        Cultivo::create($data);

        return redirect()->route('cultivos.index')
            ->with('success', 'Cultivo registrado correctamente.');
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

        if ($user->hasRole('Agricultor')) {
            abort(403, 'no tienes permiso para actualizar cultivos.');
        }

        $meses = [
            'enero',
            'febrero',
            'marzo',
            'abril',
            'mayo',
            'junio',
            'julio',
            'agosto',
            'septiembre',
            'octubre',
            'noviembre',
            'diciembre'
        ];

        $request->validate([
            'nombre' => ['required', 'regex:/^[\pL\s]+$/u', 'max:255', 'unique:cultivos,nombre,' . $cultivo->id],
            'categoria' => 'required|string|max:255',
            'cargaSuelo' => 'required|string',
            'diasCultivo' => 'required|integer|min:1|max:365',
            'siembra_inicio' => 'required|string|in:' . implode(',', $meses),
            'siembra_fin' => 'required|string|in:' . implode(',', $meses),
            'cosecha_inicio' => 'required|string|in:' . implode(',', $meses),
            'cosecha_fin' => 'required|string|in:' . implode(',', $meses),
            'descripcion' => 'nullable|string',
            'variedad' => ['nullable', 'regex:/^[\pL\s]+$/u', 'max:255'],
            'recomendaciones' => 'nullable|string',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'nombre.unique' => 'Ya existe un cultivo con este nombre.',
            'nombre.regex' => 'El nombre del cultivo solo debe contener letras y espacios.',
            'variedad.regex' => 'La variedad solo debe contener letras y espacios.',
            'diasCultivo.max' => 'Los días de cultivo no pueden superar los 365 días.',
        ]);

        $data = $request->all();
        $data['epocaSiembra'] = "{$request->siembra_inicio} - {$request->siembra_fin}";
        $data['epocaCosecha'] = "{$request->cosecha_inicio} - {$request->cosecha_fin}";

        if ($request->hasFile('imagen')) {
            if ($cultivo->imagen && file_exists(public_path($cultivo->imagen))) {
                unlink(public_path($cultivo->imagen));
            }

            $file = $request->file('imagen');
            $nombre = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('images/cultivos'), $nombre);
            $data['imagen'] = 'images/cultivos/' . $nombre;
        } else {
            $data['imagen'] = $cultivo->imagen;
        }

        $cultivo->update($data);

        return redirect()->route('cultivos.index')
            ->with('success', 'Cultivo actualizado correctamente.');
    }

    // eliminar cultivo
    public function destroy(Cultivo $cultivo)
    {
        $user = Auth::user();

        if (!$user->hasRole('Administrador')) {
            abort(403, 'no tienes permiso para eliminar cultivos.');
        }

        $tieneDetalles = DetalleRotacion::where('cultivo_id', $cultivo->id)->exists();
        $tieneEjecuciones = EjecucionRotacion::whereHas('detalle', function ($q) use ($cultivo) {
            $q->where('cultivo_id', $cultivo->id);
        })->exists();

        if ($tieneDetalles || $tieneEjecuciones) {
            return redirect()->route('cultivos.index')
                ->with('error', 'no se puede eliminar el cultivo porque está siendo utilizado en detalles o ejecuciones de rotación.');
        }

        try {
            if ($cultivo->imagen && file_exists(public_path($cultivo->imagen))) {
                unlink(public_path($cultivo->imagen));
            }

            $cultivo->delete();

            return redirect()->route('cultivos.index')
                ->with('success', 'Cultivo eliminado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('cultivos.index')
                ->with('error', 'Error al eliminar el cultivo: ' . $e->getMessage());
        }
    }
}
