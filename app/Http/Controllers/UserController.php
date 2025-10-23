<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(['role:Administrador'])->except(['perfil', 'actualizarPerfil']);
    }

    // crud general (solo administrador)
    public function index()
    {
        $users = User::with('roles')->orderBy('name')->paginate(10);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name');
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|regex:/^[\pL\s]+$/u|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|exists:roles,name',
            'estado' => 'nullable|boolean',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // subir foto
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/usuarios');
            if (!file_exists($uploadPath))
                mkdir($uploadPath, 0755, true);

            $file->move($uploadPath, $filename);
            $fotoPath = 'uploads/usuarios/' . $filename;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'estado' => $request->has('estado'),
            'foto' => $fotoPath,
        ]);

        $user->assignRole($data['role']);

        return redirect()->route('users.index')->with('success', 'usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name');
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string|exists:roles,name',
            'estado' => 'nullable|boolean',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // evitar que un administrador se quite su propio rol
        if ($user->id === auth()->id() && $data['role'] !== 'Administrador') {
            return back()->withErrors(['role' => 'no puedes quitarte el rol administrador a ti mismo.']);
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'estado' => $request->has('estado'),
        ]);

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        if ($request->hasFile('foto')) {
            // borrar foto anterior si existe
            if ($user->foto && file_exists(public_path($user->foto)))
                unlink(public_path($user->foto));
            $file = $request->file('foto');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $uploadPath = public_path('uploads/usuarios');
            if (!file_exists($uploadPath))
                mkdir($uploadPath, 0755, true);
            $file->move($uploadPath, $filename);
            $user->foto = 'uploads/usuarios/' . $filename;
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', 'usuario actualizado correctamente.');
    }

    // desactivar usuario
    public function destroy(User $user)
    {
        if ($user->id === 1) {
            return back()->withErrors(['user' => 'no puedes desactivar al administrador principal.']);
        }

        $user->estado = false;
        $user->save();

        return redirect()->route('users.index')->with('success', 'usuario desactivado.');
    }

    // alternar estado del usuario
    public function toggle(User $user)
    {
        if ($user->id === auth()->id() || $user->id === 1) {
            return back()->withErrors(['user' => 'accion no permitida.']);
        }

        $user->estado = !$user->estado;
        $user->save();

        return back()->with('success', 'estado del usuario actualizado.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    // perfil personal (tecnico / agricultor)
    public function perfil()
    {
        $usuario = auth()->user();
        return view('usuarios.perfil', compact('usuario'));
    }

    public function actualizarPerfil(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('foto')) {
            // borrar foto anterior si existe
            if ($user->foto && file_exists(public_path($user->foto))) {
                unlink(public_path($user->foto));
            }
            $file = $request->file('foto');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $uploadPath = public_path('uploads/usuarios');
            if (!file_exists($uploadPath))
                mkdir($uploadPath, 0755, true);
            $file->move($uploadPath, $filename);
            $user->foto = 'uploads/usuarios/' . $filename;
        }

        $user->save();

        return back()->with('success', 'perfil actualizado correctamente.');
    }
}
