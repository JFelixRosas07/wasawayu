<?php

namespace Tests\Feature\Parcelas;

use App\Models\Parcela;
use App\Models\User;
use App\Models\PlanRotacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestionParcelasTest extends TestCase
{
    use RefreshDatabase;

    protected $administrador;
    protected $tecnico;
    protected $agricultor;
    protected $agricultor2;

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

        $this->agricultor2 = User::factory()->create([
            'name' => 'Otro Agricultor',
            'email' => 'otroagricultor@wasawayu.com',
            'password' => bcrypt('password123'),
            'estado' => true
        ]);
        $this->agricultor2->assignRole('Agricultor');
    }

    /**
     * CP-P01: Crear Parcela Válida (Admin/Técnico)
     */
    public function test_crear_parcela_valida_como_administrador()
    {
        $poligonoValido = json_encode([
            "type" => "Feature",
            "properties" => [],
            "geometry" => [
                "type" => "Polygon",
                "coordinates" => [[
                    [-65.703955, -17.583694],
                    [-65.703569, -17.583326],
                    [-65.703269, -17.583571],
                    [-65.703805, -17.583817],
                    [-65.703955, -17.583694]
                ]]
            ]
        ]);

        $response = $this->actingAs($this->administrador)
                         ->post('/parcelas', [
                             'nombre' => 'Parcela de Prueba',
                             'extension' => 150.50,
                             'ubicacion' => 'Zona Norte',
                             'tipoSuelo' => 'Arenoso',
                             'usoSuelo' => 'Agrícola',
                             'poligono' => $poligonoValido,
                             'agricultor_id' => $this->agricultor->id
                         ]);

        $response->assertRedirect(route('parcelas.index'));
        $this->assertDatabaseHas('parcelas', [
            'nombre' => 'Parcela de Prueba',
            'extension' => 150.50,
            'ubicacion' => 'Zona Norte',
            'tipoSuelo' => 'Arenoso',
            'agricultor_id' => $this->agricultor->id
        ]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-P02: Validación de Campos Requeridos
     */
    public function test_validacion_campos_requeridos_al_crear_parcela()
    {
        $response = $this->actingAs($this->administrador)
                         ->post('/parcelas', []);

        $response->assertSessionHasErrors([
            'nombre',
            'extension',
            'ubicacion',
            'tipoSuelo',
            'usoSuelo',
            'poligono',
            'agricultor_id'
        ]);
    }

    /**
     * CP-P03: Agricultor No Puede Crear Parcelas
     */
    public function test_agricultor_no_puede_crear_parcelas()
    {
        $response = $this->actingAs($this->agricultor)
                         ->get('/parcelas/create');

        $response->assertStatus(403);
    }

    /**
     * CP-P04: Editar Parcela Existente
     */
    public function test_editar_parcela_existente()
    {
        $parcela = Parcela::create([
            'nombre' => 'Parcela Original',
            'extension' => 100.00,
            'ubicacion' => 'Zona Norte',
            'tipoSuelo' => 'Arenoso',
            'usoSuelo' => 'Agrícola',
            'poligono' => json_encode([
                "type" => "Feature",
                "properties" => [],
                "geometry" => ["type" => "Polygon", "coordinates" => [[[-65.703955, -17.583694]]]]
            ]),
            'agricultor_id' => $this->agricultor->id
        ]);

        $nuevoPoligono = json_encode([
            "type" => "Feature",
            "properties" => [],
            "geometry" => [
                "type" => "Polygon",
                "coordinates" => [[
                    [-65.704000, -17.583700],
                    [-65.703600, -17.583300],
                    [-65.703300, -17.583600],
                    [-65.703800, -17.583800],
                    [-65.704000, -17.583700]
                ]]
            ]
        ]);

        $response = $this->actingAs($this->administrador)
                         ->put("/parcelas/{$parcela->id}", [
                             'nombre' => 'Parcela Actualizada',
                             'extension' => 200.00,
                             'ubicacion' => 'Zona Sur Actualizada',
                             'tipoSuelo' => 'Arcilloso',
                             'usoSuelo' => 'Mixto',
                             'poligono' => $nuevoPoligono,
                             'agricultor_id' => $this->agricultor2->id
                         ]);

        $response->assertRedirect(route('parcelas.index'));
        $this->assertDatabaseHas('parcelas', [
            'id' => $parcela->id,
            'nombre' => 'Parcela Actualizada',
            'extension' => 200.00,
            'ubicacion' => 'Zona Sur Actualizada',
            'tipoSuelo' => 'Arcilloso',
            'agricultor_id' => $this->agricultor2->id
        ]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-P05: Eliminar Parcela Sin Planes Asociados
     */
    public function test_eliminar_parcela_sin_planes_asociados()
    {
        $parcela = Parcela::create([
            'nombre' => 'Parcela a Eliminar',
            'extension' => 100.00,
            'ubicacion' => 'Zona Test',
            'tipoSuelo' => 'Arenoso',
            'usoSuelo' => 'Agrícola',
            'poligono' => json_encode(["type" => "Feature", "geometry" => ["type" => "Polygon"]]),
            'agricultor_id' => $this->agricultor->id
        ]);

        $response = $this->actingAs($this->administrador)
                         ->delete("/parcelas/{$parcela->id}");

        $response->assertRedirect(route('parcelas.index'));
        $this->assertDatabaseMissing('parcelas', ['id' => $parcela->id]);
        $response->assertSessionHas('success');
    }

    /**
     * CP-P06: No Eliminar Parcela Con Planes Asociados
     */
    public function test_no_eliminar_parcela_con_planes_asociados()
    {
        $parcela = Parcela::create([
            'nombre' => 'Parcela con Plan',
            'extension' => 100.00,
            'ubicacion' => 'Zona Test',
            'tipoSuelo' => 'Arenoso',
            'usoSuelo' => 'Agrícola',
            'poligono' => json_encode(["type" => "Feature", "geometry" => ["type" => "Polygon"]]),
            'agricultor_id' => $this->agricultor->id
        ]);

        // Crear plan asociado a la parcela
        PlanRotacion::create([
            'parcela_id' => $parcela->id,
            'nombre' => 'Plan Test',
            'anios' => 4,
            'creado_por' => $this->administrador->id,
            'estado' => 'planificado'
        ]);

        $response = $this->actingAs($this->administrador)
                         ->delete("/parcelas/{$parcela->id}");

        $response->assertRedirect(route('parcelas.index'));
        $this->assertDatabaseHas('parcelas', ['id' => $parcela->id]);
        $response->assertSessionHas('error');
    }

    /**
     * CP-P07: Agricultor Solo Ve Sus Propias Parcelas
     */
    public function test_agricultor_solo_ve_sus_propias_parcelas()
    {
        // Crear parcelas para diferentes agricultores
        Parcela::create([
            'nombre' => 'Parcela 1',
            'extension' => 100.00,
            'ubicacion' => 'Zona Test',
            'tipoSuelo' => 'Arenoso',
            'usoSuelo' => 'Agrícola',
            'poligono' => json_encode(["type" => "Feature", "geometry" => ["type" => "Polygon"]]),
            'agricultor_id' => $this->agricultor->id
        ]);
        
        Parcela::create([
            'nombre' => 'Parcela 2', 
            'extension' => 150.00,
            'ubicacion' => 'Zona Test',
            'tipoSuelo' => 'Arcilloso',
            'usoSuelo' => 'Agrícola',
            'poligono' => json_encode(["type" => "Feature", "geometry" => ["type" => "Polygon"]]),
            'agricultor_id' => $this->agricultor->id
        ]);
        
        Parcela::create([
            'nombre' => 'Parcela Otro',
            'extension' => 200.00,
            'ubicacion' => 'Zona Test',
            'tipoSuelo' => 'Franco',
            'usoSuelo' => 'Agrícola',
            'poligono' => json_encode(["type" => "Feature", "geometry" => ["type" => "Polygon"]]),
            'agricultor_id' => $this->agricultor2->id
        ]);

        $response = $this->actingAs($this->agricultor)
                         ->get('/parcelas');

        $response->assertStatus(200);
        $parcelas = $response->viewData('parcelas');
        $this->assertCount(2, $parcelas); // Solo debe ver sus 2 parcelas
    }

    /**
     * CP-P08: Validación de JSON en Polígono
     */
    public function test_validacion_poligono_json_invalido()
    {
        $response = $this->actingAs($this->administrador)
                         ->post('/parcelas', [
                             'nombre' => 'Parcela Test',
                             'extension' => 100.00,
                             'ubicacion' => 'Test',
                             'tipoSuelo' => 'Arenoso',
                             'usoSuelo' => 'Agrícola',
                             'poligono' => 'json-invalido',
                             'agricultor_id' => $this->agricultor->id
                         ]);

        $response->assertSessionHasErrors(['poligono']);
    }

    /**
     * CP-P09: Técnico Puede Crear Parcelas
     */
    public function test_tecnico_puede_crear_parcelas()
    {
        $poligonoValido = json_encode([
            "type" => "Feature",
            "properties" => [],
            "geometry" => [
                "type" => "Polygon",
                "coordinates" => [[
                    [-65.703955, -17.583694],
                    [-65.703569, -17.583326],
                    [-65.703269, -17.583571],
                    [-65.703805, -17.583817],
                    [-65.703955, -17.583694]
                ]]
            ]
        ]);

        $response = $this->actingAs($this->tecnico)
                         ->post('/parcelas', [
                             'nombre' => 'Parcela del Técnico',
                             'extension' => 120.75,
                             'ubicacion' => 'Zona Técnica',
                             'tipoSuelo' => 'Franco',
                             'usoSuelo' => 'Agrícola',
                             'poligono' => $poligonoValido,
                             'agricultor_id' => $this->agricultor->id
                         ]);

        $response->assertRedirect(route('parcelas.index'));
        $this->assertDatabaseHas('parcelas', [
            'nombre' => 'Parcela del Técnico',
            'extension' => 120.75
        ]);
    }

    /**
     * CP-P10: Agricultor No Puede Ver Parcela de Otro Agricultor
     */
    public function test_agricultor_no_puede_ver_parcela_de_otro_agricultor()
    {
        $parcelaOtroAgricultor = Parcela::create([
            'nombre' => 'Parcela Ajena',
            'extension' => 100.00,
            'ubicacion' => 'Zona Test',
            'tipoSuelo' => 'Arenoso',
            'usoSuelo' => 'Agrícola',
            'poligono' => json_encode(["type" => "Feature", "geometry" => ["type" => "Polygon"]]),
            'agricultor_id' => $this->agricultor2->id
        ]);

        $response = $this->actingAs($this->agricultor)
                         ->get("/parcelas/{$parcelaOtroAgricultor->id}");

        $response->assertStatus(403);
    }
}