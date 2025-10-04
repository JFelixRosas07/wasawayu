<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@wasawayu.com'], // correo único
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'), // cámbialo si quieres
                'estado' => true, // importante
            ]
        );

        $admin->assignRole('Administrador');
    }
}
