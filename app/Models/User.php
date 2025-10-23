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

    // atributos que se pueden asignar en masa
    protected $fillable = [
        'name',
        'email',
        'password',
        'estado',
        'foto',
    ];

    // atributos ocultos en serializacion
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // conversion automatica de tipos
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'estado' => 'boolean',
    ];

    // relaciones

    // un usuario (agricultor) puede tener varias parcelas
    public function parcelas()
    {
        return $this->hasMany(Parcela::class, 'agricultor_id');
    }

    // un usuario puede tener varios planes de rotacion a traves de sus parcelas
    public function planesRotacion()
    {
        return $this->hasManyThrough(
            PlanRotacion::class,   // modelo final
            Parcela::class,        // modelo intermedio
            'agricultor_id',       // clave foranea en la tabla parcelas
            'parcela_id',          // clave foranea en la tabla planes_rotacion
            'id',                  // clave primaria en users
            'id'                   // clave primaria en parcelas
        );
    }
}
