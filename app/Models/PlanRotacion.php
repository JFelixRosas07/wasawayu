<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanRotacion extends Model
{
    use HasFactory;

    protected $table = 'planes_rotacion';

    protected $fillable = [
        'parcela_id',
        'nombre',
        'anios',
        'creado_por',
        'estado',
    ];

    // Relaciones
    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleRotacion::class, 'plan_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }
}
