<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ClimaController extends Controller
{
    private $apiKey;
    private $ubicacionBase;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.key');
        $this->ubicacionBase = [
            'lat' => -17.582086,
            'lon' => -65.705282,
            'nombre' => 'Wasawayu Central'
        ];
    }

    public function index()
    {
        $datosClima = $this->obtenerClimaActual();
        $pronostico = $this->obtenerPronostico();
        $alertas = $this->obtenerAlertasClimaticas();
        $ubicaciones = $this->obtenerUbicacionesParcelas();

        return view('clima.index', compact('datosClima', 'pronostico', 'alertas', 'ubicaciones'));
    }

    public function obtenerPorUbicacion(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric'
        ]);

        $clima = $this->obtenerClimaPorCoordenadas($request->lat, $request->lon);

        return response()->json($clima);
    }

    public function historico()
    {
        return view('clima.historico');
    }

    public function alertas()
    {
        $alertas = $this->obtenerAlertasClimaticas();
        return view('clima.alertas', compact('alertas'));
    }

    private function obtenerClimaActual()
    {
        return Cache::remember('clima_actual', 600, function () {
            try {
                $response = Http::timeout(10)->get("https://api.openweathermap.org/data/2.5/weather", [
                    'lat' => $this->ubicacionBase['lat'],
                    'lon' => $this->ubicacionBase['lon'],
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'es'
                ]);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    \Log::error('error api clima: ' . $response->status());
                    return $this->datosPrueba();
                }
            } catch (\Exception $e) {
                \Log::error('error obteniendo clima: ' . $e->getMessage());
                return $this->datosPrueba();
            }
        });
    }

    private function obtenerPronostico()
    {
        return Cache::remember('pronostico_5dias', 1800, function () {
            try {
                $response = Http::timeout(10)->get("https://api.openweathermap.org/data/2.5/forecast", [
                    'lat' => $this->ubicacionBase['lat'],
                    'lon' => $this->ubicacionBase['lon'],
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'es'
                ]);

                if ($response->successful()) {
                    return $response->json();
                } else {
                    return $this->pronosticoPrueba();
                }
            } catch (\Exception $e) {
                \Log::error('error obteniendo pronostico: ' . $e->getMessage());
                return $this->pronosticoPrueba();
            }
        });
    }

    private function obtenerClimaPorCoordenadas($lat, $lon)
    {
        try {
            $response = Http::timeout(10)->get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'lang' => 'es'
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error('error obteniendo clima por coordenadas: ' . $e->getMessage());
        }

        return $this->datosPrueba();
    }

    private function obtenerUbicacionesParcelas()
    {
        return [
            [
                'nombre' => 'Wasawayu Central',
                'lat' => -17.582086,
                'lon' => -65.705282,
                'tipo' => 'central'
            ],
            [
                'nombre' => 'Parcela P-01',
                'lat' => -17.583694,
                'lon' => -65.703955,
                'tipo' => 'parcela'
            ],
            [
                'nombre' => 'Parcela P-02',
                'lat' => -17.580114,
                'lon' => -65.709255,
                'tipo' => 'parcela'
            ]
        ];
    }

    private function obtenerAlertasClimaticas()
    {
        $clima = $this->obtenerClimaActual();
        $alertas = [];

        if (!$clima)
            return $alertas;

        $temperatura = $clima['main']['temp'];
        $humedad = $clima['main']['humidity'];
        $viento = $clima['wind']['speed'] * 3.6; // convertir a km/h
        $lluvia = $clima['rain']['1h'] ?? 0;

        // alerta de heladas para cultivos andinos sensibles
        if ($temperatura < 5) {
            $alertas[] = [
                'tipo' => 'helada',
                'nivel' => 'alto',
                'mensaje' => 'riesgo alto de heladas',
                'descripcion' => 'temperatura critica para cultivos sensibles',
                'icono' => 'fas fa-temperature-low',
                'accion' => 'cubrir papa, oca y papalisa. evitar riego nocturno.',
                'condicion' => "temperatura: {$temperatura}°c"
            ];
        } elseif ($temperatura < 8) {
            $alertas[] = [
                'tipo' => 'helada',
                'nivel' => 'medio',
                'mensaje' => 'posible helada nocturna',
                'descripcion' => 'temperatura baja, monitorear durante la noche',
                'icono' => 'fas fa-temperature-low',
                'accion' => 'preparar cobertores para cultivos sensibles',
                'condicion' => "temperatura: {$temperatura}°c"
            ];
        }

        // alerta de lluvia intensa
        if ($lluvia > 15) {
            $alertas[] = [
                'tipo' => 'lluvia_intensa',
                'nivel' => 'alto',
                'mensaje' => 'lluvia intensa',
                'descripcion' => 'precipitacion fuerte detectada',
                'icono' => 'fas fa-cloud-rain',
                'accion' => 'revisar drenajes y evitar labores en campo',
                'condicion' => "lluvia: {$lluvia}mm/h"
            ];
        } elseif ($lluvia > 5) {
            $alertas[] = [
                'tipo' => 'lluvia',
                'nivel' => 'medio',
                'mensaje' => 'lluvia moderada',
                'descripcion' => 'precipitacion en curso',
                'icono' => 'fas fa-cloud-rain',
                'accion' => 'adecuado para riego natural',
                'condicion' => "lluvia: {$lluvia}mm/h"
            ];
        }

        // alerta de viento fuerte
        if ($viento > 40) {
            $alertas[] = [
                'tipo' => 'viento_fuerte',
                'nivel' => 'alto',
                'mensaje' => 'vientos fuertes',
                'descripcion' => 'vientos que pueden dañar cultivos',
                'icono' => 'fas fa-wind',
                'accion' => 'asegurar invernaderos y estructuras ligeras',
                'condicion' => "viento: {$viento} km/h"
            ];
        }

        // alerta de sequia humedad baja
        if ($humedad < 30 && $lluvia == 0) {
            $alertas[] = [
                'tipo' => 'sequia',
                'nivel' => 'medio',
                'mensaje' => 'condiciones secas',
                'descripcion' => 'humedad baja sin precipitaciones',
                'icono' => 'fas fa-sun',
                'accion' => 'programar riego adicional para cultivos',
                'condicion' => "humedad: {$humedad}%"
            ];
        }

        // alerta de calor extremo
        if ($temperatura > 28) {
            $alertas[] = [
                'tipo' => 'calor',
                'nivel' => 'medio',
                'mensaje' => 'temperaturas altas',
                'descripcion' => 'calor que puede afectar cultivos',
                'icono' => 'fas fa-temperature-high',
                'accion' => 'aumentar frecuencia de riego, evitar horas pico',
                'condicion' => "temperatura: {$temperatura}°c"
            ];
        }

        return $alertas;
    }

    private function datosPrueba()
    {
        return [
            'weather' => [
                [
                    'main' => 'Clear',
                    'description' => 'cielo despejado',
                    'icon' => '01d'
                ]
            ],
            'main' => [
                'temp' => 18.5,
                'temp_min' => 12,
                'temp_max' => 24,
                'humidity' => 65,
                'pressure' => 1013
            ],
            'wind' => [
                'speed' => 3.1
            ],
            'visibility' => 10000,
            'name' => 'Cochabamba'
        ];
    }

    private function pronosticoPrueba()
    {
        return [
            'list' => [
                ['dt' => time(), 'main' => ['temp_max' => 22, 'temp_min' => 14], 'weather' => [['icon' => '01d']]],
                ['dt' => time() + 86400, 'main' => ['temp_max' => 23, 'temp_min' => 15], 'weather' => [['icon' => '02d']]],
                ['dt' => time() + 172800, 'main' => ['temp_max' => 21, 'temp_min' => 13], 'weather' => [['icon' => '03d']]],
                ['dt' => time() + 259200, 'main' => ['temp_max' => 19, 'temp_min' => 12], 'weather' => [['icon' => '10d']]],
                ['dt' => time() + 345600, 'main' => ['temp_max' => 20, 'temp_min' => 11], 'weather' => [['icon' => '04d']]]
            ]
        ];
    }
}
