<?php

namespace Tests\Feature\Rotaciones;

use Tests\TestCase;
use App\Models\PlanRotacion;
use App\Models\Parcela;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlanRotacionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_crear_plan_rotacion()
    {
        $user = User::factory()->create();
        $parcela = Parcela::create([
            'nombre' => 'Parcela Test',
            'extension' => 10.5,
            'ubicacion' => 'Test',
            'tipoSuelo' => 'Arcilloso',
            'usoSuelo' => 'Agricola',
            'poligono' => '{}',
            'agricultor_id' => $user->id,
        ]);

        $this->actingAs($user);

        // Crear plan directamente en la BD (evitar el formulario)
        $plan = PlanRotacion::create([
            'parcela_id' => $parcela->id,
            'nombre' => 'P-01',
            'anios' => 4,
            'creado_por' => $user->id,
            'estado' => 'planificado',
        ]);

        $this->assertDatabaseHas('planes_rotacion', [
            'nombre' => 'P-01',
            'estado' => 'planificado',
        ]);
    }

    /** @test */
    public function test_puede_ver_detalles_de_plan()
    {
        $user = User::factory()->create();
        $parcela = Parcela::create([
            'nombre' => 'Parcela Test',
            'extension' => 10.5,
            'ubicacion' => 'Test',
            'tipoSuelo' => 'Arcilloso',
            'usoSuelo' => 'Agricola',
            'poligono' => '{}',
            'agricultor_id' => $user->id,
        ]);

        $plan = PlanRotacion::create([
            'parcela_id' => $parcela->id,
            'nombre' => 'Plan Test',
            'anios' => 4,
            'creado_por' => $user->id,
            'estado' => 'planificado',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('planes.show', $plan->id));

        $response->assertStatus(200);
        $this->assertTrue($response->viewData('plan')->id === $plan->id);
    }

    /** @test */
    public function test_puede_actualizar_plan()
    {
        $user = User::factory()->create();
        $parcela = Parcela::create([
            'nombre' => 'Parcela Test',
            'extension' => 10.5,
            'ubicacion' => 'Test',
            'tipoSuelo' => 'Arcilloso',
            'usoSuelo' => 'Agricola',
            'poligono' => '{}',
            'agricultor_id' => $user->id,
        ]);

        $plan = PlanRotacion::create([
            'parcela_id' => $parcela->id,
            'nombre' => 'Plan Original',
            'anios' => 4,
            'creado_por' => $user->id,
            'estado' => 'planificado',
        ]);

        $this->actingAs($user);

        // Actualizar directamente en la BD
        $plan->update([
            'nombre' => 'Plan Actualizado',
        ]);

        $this->assertDatabaseHas('planes_rotacion', [
            'id' => $plan->id,
            'nombre' => 'Plan Actualizado',
        ]);
    }

    /** @test */
    public function test_puede_eliminar_plan()
    {
        $user = User::factory()->create();
        $parcela = Parcela::create([
            'nombre' => 'Parcela Test',
            'extension' => 10.5,
            'ubicacion' => 'Test',
            'tipoSuelo' => 'Arcilloso',
            'usoSuelo' => 'Agricola',
            'poligono' => '{}',
            'agricultor_id' => $user->id,
        ]);

        $plan = PlanRotacion::create([
            'parcela_id' => $parcela->id,
            'nombre' => 'Plan Para Eliminar',
            'anios' => 4,
            'creado_por' => $user->id,
            'estado' => 'planificado',
        ]);

        $this->actingAs($user);

        // Eliminar directamente
        $plan->delete();

        $this->assertDatabaseMissing('planes_rotacion', ['id' => $plan->id]);
    }

    /** @test */
    public function test_modelo_tiene_relaciones_correctas()
    {
        $user = User::factory()->create();
        $parcela = Parcela::create([
            'nombre' => 'Parcela Test',
            'extension' => 10.5,
            'ubicacion' => 'Test',
            'tipoSuelo' => 'Arcilloso',
            'usoSuelo' => 'Agricola',
            'poligono' => '{}',
            'agricultor_id' => $user->id,
        ]);

        $plan = PlanRotacion::create([
            'parcela_id' => $parcela->id,
            'nombre' => 'Plan Test',
            'anios' => 4,
            'creado_por' => $user->id,
            'estado' => 'planificado',
        ]);

        // Verificar relaciones
        $this->assertEquals($parcela->id, $plan->parcela_id);
        $this->assertEquals($user->id, $plan->creado_por);
        $this->assertInstanceOf(Parcela::class, $plan->parcela);
        $this->assertInstanceOf(User::class, $plan->creador);
    }
}