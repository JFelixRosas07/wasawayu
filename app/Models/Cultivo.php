<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cultivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'categoria',
        'cargaSuelo',
        'diasCultivo',
        'epocaSiembra',
        'epocaCosecha',
        'descripcion',
        'variedad',
        'recomendaciones',
        'imagen',
    ];

    protected $casts = [
        'diasCultivo' => 'integer',
    ];

    // relaciones

    // un cultivo puede estar en muchos detalles de rotacion planificados
    public function detallesRotacion()
    {
        return $this->hasMany(DetalleRotacion::class, 'cultivo_id');
    }

    // un cultivo puede aparecer en varias ejecuciones reales
    public function ejecucionesRotacion()
    {
        return $this->hasMany(EjecucionRotacion::class, 'cultivo_real_id');
    }
}
