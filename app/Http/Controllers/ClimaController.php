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
                    \Log::error('Error API Clima: ' . $response->status());
                    return $this->datosPrueba();
                }
            } catch (\Exception $e) {
                \Log::error('Error obteniendo clima: ' . $e->getMessage());
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
                \Log::error('Error obteniendo pronóstico: ' . $e->getMessage());
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
            \Log::error('Error obteniendo clima por coordenadas: ' . $e->getMessage());
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
        $viento = $clima['wind']['speed'] * 3.6; // Convertir a km/h
        $lluvia = $clima['rain']['1h'] ?? 0;

        // 1. ALERTA DE HELADAS (para cultivos andinos sensibles)
        if ($temperatura < 5) {
            $alertas[] = [
                'tipo' => 'helada',
                'nivel' => 'alto',
                'mensaje' => '⚠️ Riesgo ALTO de Heladas',
                'descripcion' => 'Temperatura crítica para cultivos sensibles',
                'icono' => 'fas fa-temperature-low',
                'accion' => 'Cubrir papa, oca y papalisa. Evitar riego nocturno.',
                'condicion' => "Temperatura: {$temperatura}°C"
            ];
        } elseif ($temperatura < 8) {
            $alertas[] = [
                'tipo' => 'helada',
                'nivel' => 'medio',
                'mensaje' => '⚠️ Posible Helada Nocturna',
                'descripcion' => 'Temperatura baja, monitorear durante la noche',
                'icono' => 'fas fa-temperature-low',
                'accion' => 'Preparar cobertores para cultivos sensibles',
                'condicion' => "Temperatura: {$temperatura}°C"
            ];
        }

        // 2. ALERTA DE LLUVIA INTENSA
        if ($lluvia > 15) {
            $alertas[] = [
                'tipo' => 'lluvia_intensa',
                'nivel' => 'alto',
                'mensaje' => '🌧️ Lluvia Intensa',
                'descripcion' => 'Precipitación fuerte detectada',
                'icono' => 'fas fa-cloud-rain',
                'accion' => 'Revisar drenajes y evitar labores en campo',
                'condicion' => "Lluvia: {$lluvia}mm/h"
            ];
        } elseif ($lluvia > 5) {
            $alertas[] = [
                'tipo' => 'lluvia',
                'nivel' => 'medio',
                'mensaje' => '🌧️ Lluvia Moderada',
                'descripcion' => 'Precipitación en curso',
                'icono' => 'fas fa-cloud-rain',
                'accion' => 'Adecuado para riego natural',
                'condicion' => "Lluvia: {$lluvia}mm/h"
            ];
        }

        // 3. ALERTA DE VIENTO FUERTE
        if ($viento > 40) {
            $alertas[] = [
                'tipo' => 'viento_fuerte',
                'nivel' => 'alto',
                'mensaje' => '💨 Vientos Fuertes',
                'descripcion' => 'Vientos que pueden dañar cultivos',
                'icono' => 'fas fa-wind',
                'accion' => 'Asegurar invernaderos y estructuras ligeras',
                'condicion' => "Viento: {$viento} km/h"
            ];
        }

        // 4. ALERTA DE SEQUÍA (humedad baja)
        if ($humedad < 30 && $lluvia == 0) {
            $alertas[] = [
                'tipo' => 'sequia',
                'nivel' => 'medio',
                'mensaje' => '🏜️ Condiciones Secas',
                'descripcion' => 'Humedad baja sin precipitaciones',
                'icono' => 'fas fa-sun',
                'accion' => 'Programar riego adicional para cultivos',
                'condicion' => "Humedad: {$humedad}%"
            ];
        }

        // 5. ALERTA DE CALOR EXTREMO
        if ($temperatura > 28) {
            $alertas[] = [
                'tipo' => 'calor',
                'nivel' => 'medio',
                'mensaje' => '☀️ Temperaturas Altas',
                'descripcion' => 'Calor que puede afectar cultivos',
                'icono' => 'fas fa-temperature-high',
                'accion' => 'Aumentar frecuencia de riego, evitar horas pico',
                'condicion' => "Temperatura: {$temperatura}°C"
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