<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanRotacion extends Model
{
    use HasFactory;

    protected $table = 'planes_rotacion';

    // duracion fija del plan en años
    const DURACION_ANIOS = 4;

    protected $fillable = [
        'parcela_id',
        'nombre',
        'anio_inicio',
        'creado_por',
        'estado',
    ];

    protected $casts = [
        'anio_inicio' => 'integer',
    ];

    // relaciones

    // cada plan pertenece a una parcela
    public function parcela()
    {
        return $this->belongsTo(Parcela::class);
    }

    // un plan puede tener varios detalles de rotacion
    public function detalles()
    {
        return $this->hasMany(DetalleRotacion::class, 'plan_id');
    }

    // creador del plan (usuario)
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // accessors (atributos calculados)

    // año final del ciclo (4 años de duracion)
    // protegido contra anio_inicio null
    public function getAnioFinAttribute(): int
    {
        $inicio = $this->anio_inicio ?? now()->year; // si anio_inicio es null, usar año actual
        return $inicio + (self::DURACION_ANIOS - 1);
    }

    // ciclo formateado para mostrar en vistas
    public function getCicloAttribute(): string
    {
        $inicio = $this->anio_inicio ?? now()->year;
        $fin = $this->anio_fin;
        return "{$inicio}–{$fin}";
    }

    // estado dinamico calculado segun el año actual
    public function getEstadoDinamicoAttribute(): string
    {
        $anioActual = now()->year;
        $inicio = $this->anio_inicio ?? now()->year;

        if ($anioActual < $inicio) {
            return 'planificado';
        } elseif ($anioActual > $this->anio_fin) {
            return 'finalizado';
        } else {
            return 'en_ejecucion';
        }
    }

    // badge css segun el estado dinamico
    public function getBadgeEstadoAttribute(): string
    {
        return match($this->estado_dinamico) {
            'planificado' => 'badge-info',
            'en_ejecucion' => 'badge-success',
            'finalizado' => 'badge-secondary',
            default => 'badge-secondary'
        };
    }

    // texto legible del estado
    public function getEstadoTextoAttribute(): string
    {
        return match($this->estado_dinamico) {
            'planificado' => 'Planificado',
            'en_ejecucion' => 'En ejecucion',
            'finalizado' => 'Finalizado',
            default => 'Desconocido'
        };
    }

    // metodos de validacion

    // verificar si el plan esta activo en un año especifico
    public function estaActivoEnAnio(?int $anio = null): bool
    {
        $anio = $anio ?? now()->year;
        $inicio = $this->anio_inicio ?? now()->year;
        return $anio >= $inicio && $anio <= $this->anio_fin;
    }

    // verificar si puede agregar mas detalles (maximo 4)
    public function puedeAgregarDetalle(): bool
    {
        return $this->detalles()->count() < self::DURACION_ANIOS;
    }

    // verificar si se puede eliminar el plan (sin ejecuciones)
    public function puedeEliminar(): bool
    {
        return !$this->detalles()->whereHas('ejecuciones')->exists();
    }

    // scopes (filtros reutilizables)

    // filtrar solo planes activos
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['planificado', 'en_ejecucion']);
    }

    // filtrar por parcela
    public function scopeDeParcela($query, int $parcelaId)
    {
        return $query->where('parcela_id', $parcelaId);
    }

    // ordenar por año de inicio descendente
    public function scopeMasRecientes($query)
    {
        return $query->orderBy('anio_inicio', 'desc');
    }
}
