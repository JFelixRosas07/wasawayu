<?php

namespace Tests\Feature\Cultivos;

use App\Models\Cultivo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GestionCultivosTest extends TestCase
{
    use RefreshDatabase;

    protected $administrador;
    protected $tecnico;
    protected $agricultor;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear roles necesarios
        \Spatie\Permission\Models\Role::create(['name' => 'Administrador', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'TecnicoAgronomo', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'Agricultor', 'guard_name' => 'web']);

        // Crear usuarios de prueba
        $this->administrador = User::factory()->create([
            'name' => 'Admin Principal',
            'email' => 'admin@wasawayu.com',
            'password' => bcrypt('password123'),
            'estado' => true
        ]);
        $this->administrador->assignRole('Administrador');

        $this->tecnico = User::factory()->create([
            'name' => 'Técnico Agrónomo',
            'email' => 'tecnico@wasawayu.com',
            'password' => bcrypt('password123'),
            'estado' => true
        ]);
        $this->tecnico->assignRole('TecnicoAgronomo');

        $this->agricultor = User::factory()->create([
            'name' => 'Agricultor Principal',
            'email' => 'agricultor@wasawayu.com',
            'password' => bcrypt('password123'),
            'estado' => true
        ]);
        $this->agricultor->assignRole('Agricultor');
    }

    /**
     * CP-C01: Crear Cultivo Válido (Admin/Técnico)
     */
    public function test_crear_cultivo_valido_como_administrador()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->administrador)
                         ->post('/cultivos', [
                             'nombre' => 'Maíz',
                             'categoria' => 'Cereal',
                             'cargaSuelo' => 'media',
                             'diasCultivo' => 120,
                             'epocaSiembra' => 'Septiembre - Octubre',
                             'epocaCosecha' => 'Marzo - Abril',
                             'descripcion' => 'Cereal de ciclo corto',
                             'variedad' => 'Híbrido',
                             'recomendaciones' => 'Requiere riego moderado',
                             'imagen' => UploadedFile::fake()->image('maiz.jpg')
                         ]);

        $response->assertRedirect(route('cultivos.index'));
        $this->assertDatabaseHas('cultivos', [
            'nombre' => 'Maíz',
            'categoria' => 'Cereal',
            'cargaSuelo' => 'media',
            'diasCultivo' => 120
        ]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-C02: Validación de Campos Requeridos
     */
    public function test_validacion_campos_requeridos_al_crear_cultivo()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->administrador)
                         ->post('/cultivos', []);

        $response->assertSessionHasErrors([
            'nombre',
            'categoria',
            'cargaSuelo',
            'diasCultivo',
            'epocaSiembra',
            'epocaCosecha',
            'imagen'
        ]);
    }

    /**
     * CP-C03: Validación de Días de Cultivo Positivos
     */
    public function test_validacion_dias_cultivo_positivos()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->administrador)
                         ->post('/cultivos', [
                             'nombre' => 'Test Cultivo',
                             'categoria' => 'Cereal',
                             'cargaSuelo' => 'media',
                             'diasCultivo' => 0, // Valor inválido
                             'epocaSiembra' => 'Test',
                             'epocaCosecha' => 'Test',
                             'imagen' => UploadedFile::fake()->image('test.jpg')
                         ]);

        $response->assertSessionHasErrors(['diasCultivo']);
    }

    /**
     * CP-C04: Agricultor No Puede Crear Cultivos
     */
    public function test_agricultor_no_puede_crear_cultivos()
    {
        $response = $this->actingAs($this->agricultor)
                         ->get('/cultivos/create');

        $response->assertStatus(403);
    }

    /**
     * CP-C05: Técnico Puede Crear Cultivos
     */
    public function test_tecnico_puede_crear_cultivos()
    {
        Storage::fake('public');

        $response = $this->actingAs($this->tecnico)
                         ->post('/cultivos', [
                             'nombre' => 'Trigo',
                             'categoria' => 'Cereal',
                             'cargaSuelo' => 'media',
                             'diasCultivo' => 150,
                             'epocaSiembra' => 'Mayo - Junio',
                             'epocaCosecha' => 'Noviembre - Diciembre',
                             'descripcion' => 'Cereal de invierno',
                             'variedad' => 'Inia',
                             'recomendaciones' => 'Resistente al frío',
                             'imagen' => UploadedFile::fake()->image('trigo.jpg')
                         ]);

        $response->assertRedirect(route('cultivos.index'));
        $this->assertDatabaseHas('cultivos', [
            'nombre' => 'Trigo',
            'categoria' => 'Cereal'
        ]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-C06: Editar Cultivo Existente
     */
    public function test_editar_cultivo_existente()
    {
        Storage::fake('public');

        $cultivo = Cultivo::create([
            'nombre' => 'Cultivo Original',
            'categoria' => 'Cereal',
            'cargaSuelo' => 'media',
            'diasCultivo' => 100,
            'epocaSiembra' => 'Original Siembra',
            'epocaCosecha' => 'Original Cosecha',
            'descripcion' => 'Descripción original',
            'variedad' => 'Original',
            'recomendaciones' => 'Recomendaciones originales',
            'imagen' => 'images/cultivos/original.jpg'
        ]);

        $response = $this->actingAs($this->administrador)
                         ->put("/cultivos/{$cultivo->id}", [
                             'nombre' => 'Cultivo Actualizado',
                             'categoria' => 'Tubérculo',
                             'cargaSuelo' => 'alta',
                             'diasCultivo' => 130,
                             'epocaSiembra' => 'Actualizada Siembra',
                             'epocaCosecha' => 'Actualizada Cosecha',
                             'descripcion' => 'Descripción actualizada',
                             'variedad' => 'Actualizada',
                             'recomendaciones' => 'Recomendaciones actualizadas'
                         ]);

        $response->assertRedirect(route('cultivos.index'));
        $this->assertDatabaseHas('cultivos', [
            'id' => $cultivo->id,
            'nombre' => 'Cultivo Actualizado',
            'categoria' => 'Tubérculo',
            'cargaSuelo' => 'alta',
            'diasCultivo' => 130
        ]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-C07: Eliminar Cultivo Sin Relaciones
     */
    public function test_eliminar_cultivo_sin_relaciones()
    {
        $cultivo = Cultivo::create([
            'nombre' => 'Cultivo a Eliminar',
            'categoria' => 'Cereal',
            'cargaSuelo' => 'media',
            'diasCultivo' => 100,
            'epocaSiembra' => 'Test',
            'epocaCosecha' => 'Test',
            'imagen' => 'images/cultivos/test.jpg'
        ]);

        $response = $this->actingAs($this->administrador)
                         ->delete("/cultivos/{$cultivo->id}");

        $response->assertRedirect(route('cultivos.index'));
        $this->assertDatabaseMissing('cultivos', ['id' => $cultivo->id]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-C08: Actualizar Cultivo con Nueva Imagen
     */
    public function test_actualizar_cultivo_con_nueva_imagen()
    {
        Storage::fake('public');

        $cultivo = Cultivo::create([
            'nombre' => 'Cultivo con Imagen',
            'categoria' => 'Cereal',
            'cargaSuelo' => 'media',
            'diasCultivo' => 100,
            'epocaSiembra' => 'Test',
            'epocaCosecha' => 'Test',
            'imagen' => 'images/cultivos/vieja.jpg'
        ]);

        $response = $this->actingAs($this->administrador)
                         ->put("/cultivos/{$cultivo->id}", [
                             'nombre' => 'Cultivo Actualizado',
                             'categoria' => 'Cereal',
                             'cargaSuelo' => 'media',
                             'diasCultivo' => 100,
                             'epocaSiembra' => 'Test',
                             'epocaCosecha' => 'Test',
                             'imagen' => UploadedFile::fake()->image('nueva.jpg')
                         ]);

        $response->assertRedirect(route('cultivos.index'));
        $response->assertSessionHas('success');
    }

    /**
     * CP-C09: Validación de Imagen (Formato y Tamaño)
     */
    public function test_validacion_imagen_formato_y_tamaño()
    {
        Storage::fake('public');

        // Test con archivo no imagen
        $response = $this->actingAs($this->administrador)
                         ->post('/cultivos', [
                             'nombre' => 'Test Cultivo',
                             'categoria' => 'Cereal',
                             'cargaSuelo' => 'media',
                             'diasCultivo' => 100,
                             'epocaSiembra' => 'Test',
                             'epocaCosecha' => 'Test',
                             'imagen' => UploadedFile::fake()->create('document.pdf', 1000)
                         ]);

        $response->assertSessionHasErrors(['imagen']);
    }

    /**
     * CP-C10: Técnico No Puede Eliminar Cultivos (Solo Admin)
     */
    public function test_tecnico_no_puede_eliminar_cultivos()
    {
        $cultivo = Cultivo::create([
            'nombre' => 'Cultivo Test',
            'categoria' => 'Cereal',
            'cargaSuelo' => 'media',
            'diasCultivo' => 100,
            'epocaSiembra' => 'Test',
            'epocaCosecha' => 'Test',
            'imagen' => 'images/cultivos/test.jpg'
        ]);

        $response = $this->actingAs($this->tecnico)
                         ->delete("/cultivos/{$cultivo->id}");

        $response->assertStatus(403);
    }
}