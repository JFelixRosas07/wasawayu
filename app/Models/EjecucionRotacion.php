<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EjecucionRotacion extends Model
{
    use HasFactory;

    protected $table = 'ejecuciones_rotacion';

    protected $fillable = [
        'detalle_id',
        'fecha_siembra',
        'fecha_cosecha',
        'observaciones',
        'estado',
        'creado_por',
    ];

    // ✅ AGREGAR ESTOS CASTS
    protected $casts = [
        'fecha_siembra' => 'date',
        'fecha_cosecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones (mantener las existentes)
    public function detalle()
    {
        return $this->belongsTo(DetalleRotacion::class, 'detalle_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}