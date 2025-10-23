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

    // el campo poligono se guarda como json para almacenar coordenadas
    protected $casts = [
        'poligono' => 'array',
    ];

    // relaciones

    // una parcela pertenece a un agricultor (usuario)
    public function agricultor()
    {
        return $this->belongsTo(User::class, 'agricultor_id', 'id');
    }

    // una parcela puede tener varios planes de rotacion
    public function planesRotacion()
    {
        return $this->hasMany(PlanRotacion::class, 'parcela_id');
    }

    public function planes()
    {
        return $this->hasMany(\App\Models\PlanRotacion::class, 'parcela_id');
    }

    // scope para consultar parcelas con plan en ejecucion (ejemplo)
    // permite filtrar solo aquellas parcelas que tienen planes activos
    public function scopeWithActivePlan($query)
    {
        return $query->whereHas('planes', function($q){
            $q->where('estado', 'en_ejecucion');
        });
    }
}
