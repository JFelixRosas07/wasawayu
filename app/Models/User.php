<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * Atributos que se pueden asignar en masa
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'estado',
        'foto',
    ];

    /**
     * Atributos ocultos en serialización
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversión automática de tipos
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'estado' => 'boolean',
    ];

    /*--------------------------------------------
    | RELACIONES
    ---------------------------------------------*/

    // Un usuario (agricultor) puede tener varias parcelas
    public function parcelas()
    {
        return $this->hasMany(Parcela::class, 'agricultor_id');
    }

    // Un usuario puede tener varios planes de rotación a través de sus parcelas
    public function planesRotacion()
    {
        return $this->hasManyThrough(
            PlanRotacion::class,   // Modelo final
            Parcela::class,        // Modelo intermedio
            'agricultor_id',       // Clave foránea en la tabla parcelas
            'parcela_id',          // Clave foránea en la tabla planes_rotacion
            'id',                  // Clave primaria en users
            'id'                   // Clave primaria en parcelas
        );
    }
}
