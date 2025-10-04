<?php

namespace Tests\Feature\GestionUsuarios;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GestionUsuariosTest extends TestCase
{
    use RefreshDatabase;

    protected $administrador;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles necesarios
        Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'Técnico']);
        Role::create(['name' => 'Agricultor']);

        // Crear usuario administrador
        $this->administrador = User::factory()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@wasawayu.com',
            'password' => bcrypt('password123'),
            'estado' => true
        ]);
        $this->administrador->assignRole('Administrador');
    }

    /**
     * CP-01: Crear Usuario Válido
     */
    public function test_crear_usuario_valido()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->administrador)
                         ->post('/users', [
                             'name' => 'Marco Peredo',
                             'email' => 'marcoperedo@hotmail.com',
                             'password' => 'zxcvbnm',
                             'password_confirmation' => 'zxcvbnm',
                             'role' => 'Técnico',
                             'estado' => true,
                             'foto' => UploadedFile::fake()->image('foto.jpg')
                         ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'name' => 'Marco Peredo',
            'email' => 'marcoperedo@hotmail.com'
        ]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-02: Crear Usuario con Email Duplicado
     */
    public function test_crear_usuario_con_email_duplicado()
    {
        Storage::fake('public');

        User::factory()->create([
            'name' => 'Usuario Existente',
            'email' => 'existente@wasawayu.com'
        ]);

        $response = $this->actingAs($this->administrador)
                         ->post('/users', [
                             'name' => 'Pedro Méndez',
                             'email' => 'existente@wasawayu.com',
                             'password' => 'asdfghjk',
                             'password_confirmation' => 'asdfghjk',
                             'role' => 'Técnico',
                             'estado' => true,
                             'foto' => UploadedFile::fake()->image('foto.jpg')
                         ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * CP-03: Editar Usuario Existente
     */
    public function test_editar_usuario_existente()
    {
        $user = User::factory()->create([
            'name' => 'Juan López Rojas',
            'email' => 'juan@wasawayu.com',
            'password' => bcrypt('password123')
        ]);
        $user->assignRole('Agricultor');

        $response = $this->actingAs($this->administrador)
                         ->put("/users/{$user->id}", [
                             'name' => 'Pedro Pérez',
                             'email' => 'pedro@wasawayu.com',
                             'role' => 'Técnico',
                             'estado' => true
                         ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Pedro Pérez',
            'email' => 'pedro@wasawayu.com'
        ]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-04: Desactivar Usuario - VERSIÓN CORREGIDA
     */
    public function test_desactivar_usuario()
{
    $user = User::factory()->create(['estado' => true]);
    $user->assignRole('Agricultor');

    $response = $this->actingAs($this->administrador)
                     ->delete("/users/{$user->id}");

    $response->assertRedirect(route('users.index'));
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'estado' => false
    ]);
    $response->assertSessionHas('success');
}
}