<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcela extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'extension',
        'ubicacion',
        'tipoSuelo',
        'usoSuelo',
        'poligono',
        'agricultor_id',
    ];

    // El campo poligono lo guardamos como JSON en longtext
    protected $casts = [
        'poligono' => 'array',
    ];

    /*--------------------------------------------
    | RELACIONES
    ---------------------------------------------*/

    // Una parcela pertenece a un agricultor (user)
    public function agricultor()
    {
        return $this->belongsTo(User::class, 'agricultor_id', 'id');
    }

    // Una parcela puede tener varios planes de rotación
    public function planesRotacion()
    {
        return $this->hasMany(PlanRotacion::class, 'parcela_id');
    }
}
