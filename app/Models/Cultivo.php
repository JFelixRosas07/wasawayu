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

    // Casts: para convertir atributos si fuera necesario
    protected $casts = [
        'diasCultivo' => 'integer',
    ];

    /*--------------------------------------------
    | RELACIONES
    ---------------------------------------------*/

    // Un cultivo puede estar en muchos detalles de rotación
    public function detallesRotacion()
    {
        return $this->hasMany(DetalleRotacion::class, 'cultivo_id');
    }
}
