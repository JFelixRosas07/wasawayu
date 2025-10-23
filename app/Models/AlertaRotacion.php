<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertaRotacion extends Model
{
    use HasFactory;

    protected $table = 'alertas_rotacion';

    protected $fillable = [
        'detalle_rotacion_id',
        'tipo_alerta',
        'descripcion',
        'severidad',
        'estado',
        'fecha_generada',
        'fecha_resuelta',
    ];

    // relaciones

    // una alerta pertenece a un detalle de rotacion
    public function detalle()
    {
        return $this->belongsTo(DetalleRotacion::class, 'detalle_rotacion_id');
    }

    // metodos de consulta

    // alertas activas
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }

    // alertas por severidad
    public function scopeSeveridad($query, $nivel)
    {
        return $query->where('severidad', $nivel);
    }

    // genera una alerta si la fecha de inicio del detalle
    // esta fuera de la epoca recomendada para el cultivo
    public function validarEpocaSiembra()
    {
        $cultivo = $this->cultivo; // relacion con modelo cultivo

        if (!$cultivo || empty($cultivo->epocaSiembra) || empty($this->fecha_inicio)) {
            return;
        }

        $rangoMeses = $this->mapearEpocaSiembra($cultivo->epocaSiembra);
        if (empty($rangoMeses)) {
            return;
        }

        $mesInicio = (int) \Carbon\Carbon::parse($this->fecha_inicio)->format('n');

        if (!in_array($mesInicio, $rangoMeses)) {
            // crear la alerta solo si no existe ya
            if (!$this->alertas()->where('tipo_alerta', 'fuera_epoca')->where('estado', 'activa')->exists()) {
                \App\Models\AlertaRotacion::create([
                    'detalle_rotacion_id' => $this->id,
                    'tipo_alerta' => 'fuera_epoca',
                    'descripcion' => "la fecha de siembra no coincide con la epoca recomendada ({$cultivo->epocaSiembra}).",
                    'severidad' => 'media',
                    'estado' => 'activa',
                    'fecha_generada' => now(),
                ]);
            }
        }
    }

    // convierte el texto “agosto - septiembre” en un rango de meses [8, 9]
    private function mapearEpocaSiembra(string $epoca): array
    {
        $epoca = mb_strtolower(trim($epoca));

        $mapeo = [
            'enero - febrero' => [1, 2],
            'febrero - marzo' => [2, 3],
            'marzo - abril' => [3, 4],
            'abril - mayo' => [4, 5],
            'mayo - junio' => [5, 6],
            'junio - julio' => [6, 7],
            'julio - agosto' => [7, 8],
            'agosto - septiembre' => [8, 9],
            'septiembre - octubre' => [9, 10],
            'octubre - noviembre' => [10, 11],
            'noviembre - diciembre' => [11, 12],
            'diciembre - enero' => [12, 1],
        ];

        return $mapeo[$epoca] ?? [];
    }
}
