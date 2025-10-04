<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run()
    {
        Role::firstOrCreate(['name' => 'Administrador']);
        Role::firstOrCreate(['name' => 'TecnicoAgronomo']);
        Role::firstOrCreate(['name' => 'Agricultor']);
    }
}
