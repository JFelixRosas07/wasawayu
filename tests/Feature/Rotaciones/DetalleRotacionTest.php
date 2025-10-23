<?php

namespace Tests\Feature\Rotaciones;

use Tests\TestCase;
use App\Models\DetalleRotacion;
use App\Models\PlanRotacion;
use App\Models\Parcela;
use App\Models\User;
use App\Models\Cultivo;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DetalleRotacionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_puede_crear_detalle_con_cultivo()
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

        $cultivo = Cultivo::create([
            'nombre' => 'Maíz',
            'categoria' => 'Cereal',
            'epocaSiembra' => 'Primavera',
            'epocaCosecha' => 'Otoño',
            'diasCultivo' => 120,
            'cargaSuelo' => 'media',
            'recomendaciones' => 'Requiere riego moderado',
        ]);

        $this->actingAs($user);

        // Crear detalle directamente en la BD
        $detalle = DetalleRotacion::create([
            'plan_id' => $plan->id,
            'anio' => 1,
            'cultivo_id' => $cultivo->id,
            'es_descanso' => false,
            'fecha_inicio' => '2024-09-01',
            'fecha_fin' => '2024-12-31',
        ]);

        $this->assertDatabaseHas('detalles_rotacion', [
            'plan_id' => $plan->id,
            'anio' => 1,
            'cultivo_id' => $cultivo->id,
            'es_descanso' => false,
        ]);
    }

    /** @test */
    public function test_puede_crear_detalle_con_descanso()
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

        // Crear detalle de descanso directamente en la BD
        $detalle = DetalleRotacion::create([
            'plan_id' => $plan->id,
            'anio' => 2,
            'cultivo_id' => null,
            'es_descanso' => true,
            'fecha_inicio' => '2025-01-01',
            'fecha_fin' => '2025-12-31',
        ]);

        $this->assertDatabaseHas('detalles_rotacion', [
            'plan_id' => $plan->id,
            'anio' => 2,
            'cultivo_id' => null,
            'es_descanso' => true,
        ]);
    }

    /** @test */
    public function test_puede_actualizar_detalle()
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

        $cultivo = Cultivo::create([
            'nombre' => 'Trigo',
            'categoria' => 'Cereal',
            'epocaSiembra' => 'Otoño',
            'epocaCosecha' => 'Verano',
            'diasCultivo' => 150,
            'cargaSuelo' => 'media',
            'recomendaciones' => 'Requiere fertilización',
        ]);

        $detalle = DetalleRotacion::create([
            'plan_id' => $plan->id,
            'anio' => 1,
            'cultivo_id' => $cultivo->id,
            'es_descanso' => false,
            'fecha_inicio' => '2024-10-01',
            'fecha_fin' => '2025-03-01',
        ]);

        $this->actingAs($user);

        // Actualizar detalle directamente en la BD
        $detalle->update([
            'fecha_inicio' => '2024-10-15',
            'fecha_fin' => '2025-03-15',
        ]);

        $this->assertDatabaseHas('detalles_rotacion', [
            'id' => $detalle->id,
            'fecha_inicio' => '2024-10-15',
            'fecha_fin' => '2025-03-15',
        ]);
    }

    /** @test */
    public function test_puede_eliminar_detalle()
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

        $detalle = DetalleRotacion::create([
            'plan_id' => $plan->id,
            'anio' => 1,
            'cultivo_id' => null,
            'es_descanso' => true,
            'fecha_inicio' => '2024-01-01',
            'fecha_fin' => '2024-12-31',
        ]);

        $this->actingAs($user);

        // Eliminar detalle directamente
        $detalle->delete();

        $this->assertDatabaseMissing('detalles_rotacion', ['id' => $detalle->id]);
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

        $cultivo = Cultivo::create([
            'nombre' => 'Papa',
            'categoria' => 'Tubérculo',
            'epocaSiembra' => 'Primavera',
            'epocaCosecha' => 'Verano',
            'diasCultivo' => 90,
            'cargaSuelo' => 'alta',
            'recomendaciones' => 'Controlar plagas',
        ]);

        $detalle = DetalleRotacion::create([
            'plan_id' => $plan->id,
            'anio' => 1,
            'cultivo_id' => $cultivo->id,
            'es_descanso' => false,
            'fecha_inicio' => '2024-09-01',
            'fecha_fin' => '2024-12-01',
        ]);

        // Verificar relaciones
        $this->assertEquals($plan->id, $detalle->plan_id);
        $this->assertEquals($cultivo->id, $detalle->cultivo_id);
        $this->assertInstanceOf(PlanRotacion::class, $detalle->plan);
        $this->assertInstanceOf(Cultivo::class, $detalle->cultivo);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $detalle->ejecuciones);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $detalle->alertas);
    }
}