<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleRotacion extends Model
{
    use HasFactory;

    protected $table = 'detalles_rotacion';

    protected $fillable = [
        'plan_id',
        'anio',
        'cultivo_id',
        'es_descanso',
        'fecha_inicio',
        'fecha_fin',
        'alerta',
    ];

    // agregar estos casts
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_descanso' => 'boolean',
    ];

    // relaciones

    public function plan()
    {
        return $this->belongsTo(PlanRotacion::class, 'plan_id');
    }

    public function cultivo()
    {
        return $this->belongsTo(Cultivo::class);
    }

    public function ejecuciones()
    {
        return $this->hasMany(EjecucionRotacion::class, 'detalle_id');
    }

    public function alertas()
    {
        return $this->hasMany(AlertaRotacion::class, 'detalle_rotacion_id');
    }

    // validacion agronomica automatica

    // hook de eloquent: se ejecuta automaticamente cada vez
    // que se crea o actualiza un detalle de rotacion
    protected static function booted()
    {
        static::saved(function ($detalle) {
            $detalle->validarEpocaSiembra();
        });
    }

    // genera o resuelve la alerta “fuera de epoca”
    // segun la fecha de siembra comparada con la epoca del cultivo
    public function validarEpocaSiembra()
    {
        // si no hay cultivo o esta marcado como descanso, salir
        if ($this->es_descanso || !$this->cultivo || empty($this->cultivo->epocaSiembra)) {
            return;
        }

        $rangoMeses = $this->mapearEpocaSiembra($this->cultivo->epocaSiembra);
        if (empty($rangoMeses) || empty($this->fecha_inicio)) {
            return;
        }

        $mesInicio = (int) \Carbon\Carbon::parse($this->fecha_inicio)->format('n');
        $alertaActiva = $this->alertas()->where('tipo_alerta', 'fuera_epoca')->where('estado', 'activa')->first();

        if (!in_array($mesInicio, $rangoMeses)) {
            // si esta fuera de epoca y no existe alerta activa -> crear
            if (!$alertaActiva) {
                \App\Models\AlertaRotacion::create([
                    'detalle_rotacion_id' => $this->id,
                    'tipo_alerta' => 'fuera_epoca',
                    'descripcion' => "la fecha de siembra no coincide con la epoca recomendada ({$this->cultivo->epocaSiembra}).",
                    'severidad' => 'media',
                    'estado' => 'activa',
                    'fecha_generada' => now(),
                ]);
            }
        } else {
            // si esta dentro de epoca y habia una alerta -> marcar como resuelta
            if ($alertaActiva) {
                $alertaActiva->update([
                    'estado' => 'resuelta',
                    'fecha_resuelta' => now(),
                ]);
            }
        }
    }

    // convierte texto como “agosto - septiembre” en un rango numerico [8, 9]
    private function mapearEpocaSiembra(string $epoca): array
    {
        $epoca = mb_strtolower(trim($epoca));

        $mapeo = [
            'enero - febrero' => [1, 2],
            'febrero - marzo' => [2, 3],
            'marzo - abril' => [3, 4],
            'abril - mayo' => [4, 5],
            'mayo - junio' => [5, 6],
            'junio - julio' => [6, 7],
            'julio - agosto' => [7, 8],
            'agosto - septiembre' => [8, 9],
            'septiembre - octubre' => [9, 10],
            'octubre - noviembre' => [10, 11],
            'noviembre - diciembre' => [11, 12],
            'diciembre - enero' => [12, 1],
        ];

        return $mapeo[$epoca] ?? [];
    }
}
