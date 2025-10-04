<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    /**
     * Esta instrucción es CLAVE.
     * Le dice a Laravel que ejecute las migraciones en una base de datos de prueba
     * en memoria antes de cada test, y la borre después.
     * Así, cada prueba se ejecuta en un ambiente limpio y aislado.
     */
    use RefreshDatabase;

    /**
     * Prueba #1: La página de login se muestra correctamente.
     */
    public function test_la_pantalla_de_login_se_puede_mostrar(): void
    {
        // Act: Realizamos una petición GET a la ruta '/login'
        $response = $this->get('/login');

        // Assert: Verificamos que la respuesta fue exitosa (código 200)
        $response->assertStatus(200);
        // Assert: Verificamos que se está mostrando la vista correcta (opcional pero recomendado)
        $response->assertViewIs('auth.login');
    }

    /**
     * Prueba #2: Un usuario activo puede iniciar sesión. (El "Happy Path")
     */
    public function test_un_usuario_activo_puede_iniciar_sesion(): void
    {
        // Arrange: Creamos un usuario de prueba que esté ACTIVO.
        $user = User::factory()->create([
            'email' => 'usuario.activo@wasawayu.com',
            'password' => bcrypt('password123'),
            'estado' => true,
        ]);

        // Act: Hacemos una petición POST a la ruta de login con las credenciales correctas.
        $response = $this->post(route('login'), [
            'email' => 'usuario.activo@wasawayu.com',
            'password' => 'password123',
        ]);

        // Assert:
        // Verificamos que el usuario fue autenticado correctamente.
        $this->assertAuthenticatedAs($user);
        // Verificamos que fue redirigido a la ruta '/home'.
        $response->assertRedirect('/home');
    }

    /**
     * Prueba #3: Un usuario INACTIVO no puede iniciar sesión.
     * Esta prueba es específica para tu lógica personalizada.
     */
    public function test_un_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        // Arrange: Creamos un usuario de prueba que esté INACTIVO.
        $user = User::factory()->create([
            'email' => 'usuario.inactivo@wasawayu.com',
            'password' => bcrypt('password123'),
            'estado' => false, // La clave de esta prueba
        ]);

        // Act: Intentamos iniciar sesión con sus credenciales.
        $response = $this->post(route('login'), [
            'email' => 'usuario.inactivo@wasawayu.com',
            'password' => 'password123',
        ]);

        // Assert:
        // Verificamos que el usuario NO fue autenticado (sigue siendo "invitado").
        $this->assertGuest();
        // Verificamos que la sesión contiene un error de validación para el campo 'email'.
        $response->assertSessionHasErrors('email');
    }

    /**
     * Prueba #4: Un usuario con contraseña incorrecta no puede iniciar sesión.
     */
    public function test_un_usuario_con_password_incorrecto_no_puede_iniciar_sesion(): void
    {
        // Arrange: Creamos un usuario.
        $user = User::factory()->create([
            'email' => 'test@wasawayu.com',
            'password' => bcrypt('password-correcto'),
            'estado' => true,
        ]);

        // Act: Intentamos iniciar sesión con una contraseña incorrecta.
        $response = $this->post(route('login'), [
            'email' => 'test@wasawayu.com',
            'password' => 'password-incorrecto',
        ]);

        // Assert:
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
