<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parcela;

class ParcelaGeoJsonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Administrador|TecnicoAgronomo|Agricultor');
    }

    public function geojson(Request $request)
    {
        $user = auth()->user();

        // si es agricultor, usa su propio id
        $agricultorId = $user->hasRole('Agricultor')
            ? $user->id
            : $request->get('agricultor_id');

        // traer parcelas con agricultor y ultimo plan activo
        $parcelas = Parcela::when($agricultorId, fn($q) => 
                $q->where('agricultor_id', $agricultorId)
            )
            ->with([
                'agricultor:id,name',
                'planes' => function ($q) {
                    $q->orderBy('anio_inicio', 'desc')
                      ->with(['detalles.cultivo']);
                }
            ])
            ->get(['id', 'nombre', 'poligono', 'agricultor_id']);

        // formatear a geojson
        $features = $parcelas->map(function ($p) {
            $json = is_array($p->poligono) ? $p->poligono : json_decode($p->poligono, true);
            if (!$json) return null;

            $geometry = $json['geometry'] ?? ($json['type'] === 'Feature' ? $json['geometry'] : $json);

            // determinar cultivo actual
            $ultimoPlan = $p->planes->first();
            $cultivoActual = 'Libre';
            if ($ultimoPlan && $ultimoPlan->detalles->count()) {
                $detalle = $ultimoPlan->detalles
                    ->sortByDesc('anio')
                    ->first();
                if ($detalle->es_descanso) {
                    $cultivoActual = 'Descanso';
                } elseif ($detalle->cultivo) {
                    $cultivoActual = $detalle->cultivo->nombre;
                }
            }

            return [
                'type' => 'Feature',
                'properties' => [
                    'nombre' => $p->nombre,
                    'agricultor' => optional($p->agricultor)->name ?? 'Sin asignar',
                    'cultivo' => $cultivoActual,
                ],
                'geometry' => $geometry,
            ];
        })->filter();

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features->values()
        ]);
    }
}
