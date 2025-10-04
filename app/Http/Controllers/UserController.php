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
        // Middleware que garantiza que solo los usuarios autenticados
        // con rol Administrador accedan a este controlador.
        $this->middleware(['auth', 'role:Administrador']);
    }

    public function index()
    {
        // Obtiene todos los usuarios junto con sus roles,
        // ordenados alfabéticamente y paginados de 10 en 10.
        $users = User::with('roles')->orderBy('name')->paginate(10);

        // Retorna la vista principal de usuarios, enviando la colección obtenida.
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name');
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        // Validación de los campos ingresados en el formulario
        $data = $request->validate([
            'name' => 'required|string|regex:/^[\pL\s]+$/u|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|exists:roles,name',
            'estado' => 'nullable|boolean',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Procesamiento de la foto y guardado en carpeta pública
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            // Crear directorio si no existe
            $uploadPath = public_path('uploads/usuarios');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);
            $fotoPath = 'uploads/usuarios/' . $filename;
        }

        // Creación del usuario con hash en la contraseña
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'estado' => $request->has('estado'),
            'foto' => $fotoPath,
        ]);

        // Asignación del rol mediante Spatie
        $user->assignRole($data['role']);

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
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
        ], [
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.max' => 'La contraseña no debe tener más de 20 caracteres.',
        ]);

        // Seguridad: impedir que el admin se quite su propio rol
        if ($user->id === auth()->id() && $data['role'] !== 'Administrador') {
            return back()->withErrors(['role' => 'No puedes quitarte el rol Administrador a ti mismo.']);
        }

        // Actualizar campos básicos
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->estado = $request->has('estado');

        // Actualizar contraseña si se proporciona
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Manejar actualización de foto
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($user->foto && file_exists(public_path($user->foto))) {
                unlink(public_path($user->foto));
            }

            // Subir nueva foto
            $file = $request->file('foto');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            $uploadPath = public_path('uploads/usuarios');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $file->move($uploadPath, $filename);
            $user->foto = 'uploads/usuarios/' . $filename;
        }

        $user->save();
        $user->syncRoles([$data['role']]);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        // Protección: no permitir desactivar al administrador principal
        if ($user->id === 1) {
            return back()->withErrors(['user' => 'No puedes desactivar al administrador principal del sistema.']);
        }

        $user->estado = false;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Usuario desactivado.');
    }

    public function toggle(User $user)
    {
        // Protección: no permitir que un usuario se desactive a sí mismo
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'No puedes cambiar el estado de tu propia cuenta.']);
        }

        if ($user->id === 1) {
            return back()->withErrors(['user' => 'No puedes desactivar al administrador principal del sistema.']);
        }

        $user->estado = !$user->estado;
        $user->save();

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }
}