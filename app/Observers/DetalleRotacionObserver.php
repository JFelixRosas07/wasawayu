<?php

namespace App\Observers;

use App\Models\DetalleRotacion;
use App\Models\AlertaRotacion;
use Illuminate\Support\Facades\Log;

class DetalleRotacionObserver
{
    // se ejecuta cuando se crea o actualiza un detalle de rotacion
    // analiza automaticamente la rotacion agricola y genera alertas
    public function saved(DetalleRotacion $detalle)
    {
        try {
            $this->analizarRotacion($detalle);
        } catch (\Throwable $e) {
            Log::error('error al analizar rotacion: ' . $e->getMessage());
        }
    }

    // analiza la rotacion agricola en base al historial de la parcela
    // genera alertas automaticas segun reglas agronomicas
    private function analizarRotacion(DetalleRotacion $detalle)
    {
        $plan = $detalle->plan;
        $parcela = $plan->parcela;

        if (!$parcela) return;

        // eliminar alertas antiguas para este detalle (evitar duplicados)
        AlertaRotacion::where('detalle_rotacion_id', $detalle->id)->delete();

        // obtener los ultimos 4 años de rotaciones (del plan actual y anteriores)
        $detallesPrevios = \App\Models\DetalleRotacion::whereHas('plan', function ($q) use ($parcela) {
                $q->where('parcela_id', $parcela->id);
            })
            ->where('anio', '<', $detalle->anio)
            ->orderByDesc('anio')
            ->take(4)
            ->with('cultivo')
            ->get();

        // no hay historial (primer plan de la parcela)
        if ($detallesPrevios->isEmpty()) {
            AlertaRotacion::create([
                'detalle_rotacion_id' => $detalle->id,
                'tipo_alerta' => 'sin_historial',
                'descripcion' => 'es el primer plan registrado para esta parcela, no se puede evaluar la rotacion previa.',
                'severidad' => 'baja',
            ]);
            return;
        }

        // evaluar carga acumulada de cultivos de alta carga
        $cargaAltaConsecutiva = 0;
        foreach ($detallesPrevios as $d) {
            if ($d->cultivo && $d->cultivo->carga_suelo === 'alta') {
                $cargaAltaConsecutiva++;
            }
        }

        // si hubo 3 o mas cultivos de alta carga consecutivos, generar alerta
        if ($cargaAltaConsecutiva >= 3) {
            AlertaRotacion::create([
                'detalle_rotacion_id' => $detalle->id,
                'tipo_alerta' => 'carga_excesiva',
                'descripcion' => 'la parcela tuvo varios cultivos de alta carga consecutivos. se recomienda descanso o leguminosas.',
                'severidad' => 'alta',
            ]);
        }

        // evaluar cultivo repetido en los ultimos años
        $cultivoActual = $detalle->cultivo?->nombre ?? null;
        if ($cultivoActual && $detallesPrevios->pluck('cultivo.nombre')->contains($cultivoActual)) {
            AlertaRotacion::create([
                'detalle_rotacion_id' => $detalle->id,
                'tipo_alerta' => 'cultivo_repetido',
                'descripcion' => "el cultivo {$cultivoActual} ya fue sembrado en los ultimos años. se recomienda variar para evitar agotamiento del suelo.",
                'severidad' => 'media',
            ]);
        }

        // evaluar si hubo falta de años de descanso
        $tuvoDescanso = $detallesPrevios->contains(fn($d) => $d->es_descanso);
        if (!$tuvoDescanso) {
            AlertaRotacion::create([
                'detalle_rotacion_id' => $detalle->id,
                'tipo_alerta' => 'sin_descanso',
                'descripcion' => 'no se registro ningun año de descanso en los ultimos 4 años.',
                'severidad' => 'media',
            ]);
        }
    }
}
