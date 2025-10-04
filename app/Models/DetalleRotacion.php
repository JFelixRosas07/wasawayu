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

    // ✅ AGREGAR ESTOS CASTS
    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'es_descanso' => 'boolean',
    ];

    // Relaciones (mantener las existentes)
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
}