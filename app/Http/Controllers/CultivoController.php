<?php

namespace App\Http\Controllers;

use App\Models\Cultivo;
use Illuminate\Http\Request;

class CultivoController extends Controller
{
    /**
     * Mostrar listado de cultivos
     */
    public function index()
    {
        $cultivos = Cultivo::latest()->paginate(10);
        return view('cultivos.index', compact('cultivos'));
    }

    /**
     * Mostrar formulario de creación
     */
    public function create()
    {
        $categorias = ['Cereal', 'Tubérculo', 'Leguminosa', 'Hortaliza', 'Frutal'];
        $cargas = ['alta', 'media', 'baja', 'regenerativa'];

        return view('cultivos.create', compact('categorias', 'cargas'));
    }

    /**
     * Guardar un nuevo cultivo
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'categoria'       => 'required|string|max:255',
            'cargaSuelo'      => 'required|string',
            'diasCultivo'     => 'required|integer|min:1',
            'epocaSiembra'    => 'required|string|max:255',
            'epocaCosecha'    => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'variedad'        => 'nullable|string|max:255',
            'recomendaciones' => 'nullable|string',
            'imagen'          => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            $file = $request->file('imagen');
            $nombre = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/cultivos'), $nombre);
            $data['imagen'] = 'images/cultivos/'.$nombre;
        }

        Cultivo::create($data);

        return redirect()->route('cultivos.index')
            ->with('success', 'Cultivo registrado correctamente.');
    }

    /**
     * Mostrar un cultivo
     */
    public function show(Cultivo $cultivo)
    {
        return view('cultivos.show', compact('cultivo'));
    }

    /**
     * Formulario para editar
     */
    public function edit(Cultivo $cultivo)
    {
        $categorias = ['Cereal', 'Tubérculo', 'Leguminosa', 'Hortaliza', 'Frutal'];
        $cargas = ['alta', 'media', 'baja', 'regenerativa'];

        return view('cultivos.edit', compact('cultivo', 'categorias', 'cargas'));
    }

    /**
     * Actualizar cultivo
     */
    public function update(Request $request, Cultivo $cultivo)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'categoria'       => 'required|string|max:255',
            'cargaSuelo'      => 'required|string',
            'diasCultivo'     => 'required|integer|min:1',
            'epocaSiembra'    => 'required|string|max:255',
            'epocaCosecha'    => 'required|string|max:255',
            'descripcion'     => 'nullable|string',
            'variedad'        => 'nullable|string|max:255',
            'recomendaciones' => 'nullable|string',
            'imagen'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('imagen')) {
            // Borrar imagen anterior si existe en public/images/cultivos
            if ($cultivo->imagen && file_exists(public_path($cultivo->imagen))) {
                unlink(public_path($cultivo->imagen));
            }

            $file = $request->file('imagen');
            $nombre = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('images/cultivos'), $nombre);
            $data['imagen'] = 'images/cultivos/'.$nombre;
        }

        $cultivo->update($data);

        return redirect()->route('cultivos.index')
            ->with('success', 'Cultivo actualizado correctamente.');
    }

    /**
     * Eliminar cultivo
     */
    public function destroy(Cultivo $cultivo)
    {
        if ($cultivo->imagen && file_exists(public_path($cultivo->imagen))) {
            unlink(public_path($cultivo->imagen));
        }

        $cultivo->delete();

        return redirect()->route('cultivos.index')
            ->with('success', 'Cultivo eliminado correctamente.');
    }
}
