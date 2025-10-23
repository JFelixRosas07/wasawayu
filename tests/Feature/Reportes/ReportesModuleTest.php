<?php

namespace Tests\Feature\Reportes;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportesModuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_vista_reporte_parcelas_agricultor_responde_correctamente()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/reportes/parcelas-agricultor');
        $response->assertOk();
        $response->assertViewIs('reportes.partials.reporte-parcelas-agricultor');
    }

    /** @test */
    public function test_vista_reporte_cultivos_del_sistema_responde_correctamente()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/reportes/cultivos-sistema');
        $response->assertOk();
        $response->assertViewIs('reportes.partials.reporte-cultivos');
    }

    /** @test */
    public function test_vista_reporte_rotacion_por_agricultor_responde_correctamente()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/reportes/rotacion-agricultor');
        $response->assertOk();
        $response->assertViewIs('reportes.partials.reporte-rotacion-agricultor');
    }

    /** @test */
    public function test_vista_reporte_ejecuciones_reales_responde_correctamente()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/ejecuciones-sistema');
        $response->assertOk();
        $response->assertViewIs('reportes.partials.reporte-ejecuciones');
    }
}
