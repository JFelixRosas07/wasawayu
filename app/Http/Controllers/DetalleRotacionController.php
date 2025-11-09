<?php

namespace App\Http\Controllers;

use App\Models\DetalleRotacion;
use App\Models\PlanRotacion;
use App\Models\Cultivo;
use App\Models\Parcela;
use App\Models\AlertaRotacion;
use Illuminate\Http\Request;

class DetalleRotacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Administrador|TecnicoAgronomo|Agricultor');
    }

    // Crear detalle
    public function create($plan_id)
    {
        $plan = PlanRotacion::with('parcela')->findOrFail($plan_id);
        $user = auth()->user();

        if ($user->hasRole('Agricultor') && $plan->parcela->agricultor_id !== $user->id) {
            abort(403, 'No tienes permiso para agregar detalles en este plan.');
        }

        $cultivos = Cultivo::all();
        return view('rotaciones.detalles.create', compact('plan', 'cultivos'));
    }

    // Guardar detalle
    public function store(Request $request, $plan_id)
    {
        $plan = PlanRotacion::with('parcela')->findOrFail($plan_id);
        $user = auth()->user();
        $parcelaId = $request->query('parcela_id');

        if ($user->hasRole('Agricultor') && $plan->parcela->agricultor_id !== $user->id) {
            abort(403, 'No tienes permiso para agregar detalles en este plan.');
        }

        $request->validate([
            'anio' => 'required|integer|min:1',
            'cultivo_id' => 'nullable|exists:cultivos,id',
            'es_descanso' => 'nullable|boolean',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if (!$request->boolean('es_descanso') && !$request->cultivo_id) {
            return back()
                ->withErrors(['cultivo_id' => 'Debe seleccionar un cultivo o marcar el año como descanso.'])
                ->withInput();
        }

        $detalle = DetalleRotacion::create([
            'plan_id' => $plan->id,
            'anio' => $request->anio,
            'cultivo_id' => $request->boolean('es_descanso') ? null : $request->cultivo_id,
            'es_descanso' => $request->boolean('es_descanso'),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        $this->reevaluarPlan($plan);

        return redirect()
            ->route('planes.show', ['plan_id' => $plan->id, 'parcela_id' => $parcelaId])
            ->with('success', 'Detalle agregado y alertas reevaluadas correctamente.');
    }

    // Editar detalle
    public function edit(DetalleRotacion $detalle)
    {
        $detalle->load('plan.parcela');
        $user = auth()->user();

        if ($user->hasRole('Agricultor') && $detalle->plan->parcela->agricultor_id !== $user->id) {
            abort(403, 'No tienes permiso para editar este detalle.');
        }

        $cultivos = Cultivo::all();
        return view('rotaciones.detalles.edit', compact('detalle', 'cultivos'));
    }

    // Actualizar detalle
    public function update(Request $request, DetalleRotacion $detalle)
    {
        $detalle->load('plan.parcela');
        $user = auth()->user();
        $parcelaId = $request->query('parcela_id');

        if ($user->hasRole('Agricultor') && $detalle->plan->parcela->agricultor_id !== $user->id) {
            abort(403, 'No tienes permiso para actualizar este detalle.');
        }

        $request->validate([
            'anio' => 'required|integer|min:1',
            'cultivo_id' => 'nullable|exists:cultivos,id',
            'es_descanso' => 'nullable|boolean',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        if (!$request->boolean('es_descanso') && !$request->cultivo_id) {
            return back()
                ->withErrors(['cultivo_id' => 'Debe seleccionar un cultivo o marcar el año como descanso.'])
                ->withInput();
        }

        $detalle->update([
            'anio' => $request->anio,
            'cultivo_id' => $request->boolean('es_descanso') ? null : $request->cultivo_id,
            'es_descanso' => $request->boolean('es_descanso'),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        $this->reevaluarPlan($detalle->plan);

        return redirect()
            ->route('planes.show', ['plan_id' => $detalle->plan->id, 'parcela_id' => $parcelaId])
            ->with('success', 'Detalle actualizado y alertas reevaluadas correctamente.');
    }

    // Reevaluar alertas de todo el plan
    private function reevaluarPlan(PlanRotacion $plan)
{
    // Eliminar alertas globales previas
    AlertaRotacion::whereHas('detalle', fn($q) =>
        $q->where('plan_id', $plan->id)
    )->whereIn('tipo_alerta', ['sin_leguminosa', 'sin_descanso_prolongado'])
     ->delete();

    // Reevaluar cada detalle individualmente
    foreach ($plan->detalles as $detalle) {
        $this->evaluarAlertas($detalle);
    }

    // Contar cuántos años (detalles) ya existen en el plan
    $cantidadAnios = $plan->detalles->count();

    // Solo evaluar alertas globales si hay más de 1 año (segundo año en adelante)
    if ($cantidadAnios < 2) {
        return; // 🔹 No generar alertas globales aún
    }

    $anioActual = now()->year;

    // --- Verificar leguminosas (últimos 2 años) ---
    $anioLimiteLegum = $anioActual - 2;
    $tieneLeguminosa = DetalleRotacion::whereHas('plan', fn($q) =>
            $q->where('parcela_id', $plan->parcela_id))
        ->whereHas('cultivo', fn($q) => $q->where('categoria', 'leguminosa'))
        ->whereYear('fecha_inicio', '>=', $anioLimiteLegum)
        ->exists();

    if (!$tieneLeguminosa && $cantidadAnios >= 2) {
        $primerDetalle = $plan->detalles->first();
        AlertaRotacion::create([
            'detalle_rotacion_id' => $primerDetalle?->id,
            'tipo_alerta' => 'sin_leguminosa',
            'descripcion' => "No se registran cultivos leguminosos en los últimos 2 años. "
                . "Por qué importa: las leguminosas fijan nitrógeno y mejoran la fertilidad. "
                . "Riesgos: agotamiento del nitrógeno y menor rendimiento a largo plazo. "
                . "Recomendación: incluir leguminosas (haba, arveja, frijol) en la rotación.",
            'severidad' => 'media',
            'estado' => 'activa',
            'fecha_generada' => now(),
        ]);
    }

    // --- Verificar descanso prolongado (últimos 3 años) ---
    if ($cantidadAnios >= 3) { // 🔹 Solo desde el tercer año
        $anioLimiteDescanso = $anioActual - 3;
        $tieneDescanso = DetalleRotacion::whereHas('plan', fn($q) =>
                $q->where('parcela_id', $plan->parcela_id))
            ->where('es_descanso', true)
            ->whereYear('fecha_inicio', '>=', $anioLimiteDescanso)
            ->exists();

        if (!$tieneDescanso) {
            $primerDetalle = $plan->detalles->first();
            AlertaRotacion::create([
                'detalle_rotacion_id' => $primerDetalle?->id,
                'tipo_alerta' => 'sin_descanso_prolongado',
                'descripcion' => "No se han registrado años de descanso en los últimos 3 años. "
                    . "Por qué importa: la falta de descanso reduce la materia orgánica y acelera la degradación del suelo. "
                    . "Riesgos: pérdida sostenida de productividad y mayores costos de fertilización. "
                    . "Recomendación: planificar un año de descanso o usar cultivos de cobertura.",
                'severidad' => 'alta',
                'estado' => 'activa',
                'fecha_generada' => now(),
            ]);
        }
    }
}




    // Evaluar alertas agronómicas basadas en años reales
    private function evaluarAlertas(DetalleRotacion $detalle)
{
    $plan = $detalle->plan;
    $parcela = $plan->parcela;
    $cultivoActual = $detalle->cultivo;

    // Limpiar alertas previas de este detalle
    AlertaRotacion::where('detalle_rotacion_id', $detalle->id)
        ->whereNotIn('tipo_alerta', ['sin_leguminosa', 'sin_descanso_prolongado'])
        ->delete();

    // Obtener año real de siembra
    $anioActual = $detalle->fecha_inicio ? \Carbon\Carbon::parse($detalle->fecha_inicio)->year : null;
    if (!$anioActual) {
        return; // sin fecha, no se puede evaluar
    }

    // Año de inicio del plan
    $anioInicioPlan = $plan->detalles
        ->filter(fn($d) => $d->fecha_inicio)
        ->min(fn($d) => \Carbon\Carbon::parse($d->fecha_inicio)->year);

    // Caso especial: descanso
    if ($detalle->es_descanso) {
        AlertaRotacion::create([
            'detalle_rotacion_id' => $detalle->id,
            'tipo_alerta' => 'descanso_programado',
            'descripcion' => 'Año de descanso: el suelo se recupera naturalmente mediante procesos biológicos y aumento de materia orgánica. No representa riesgo agronómico, pero debe planificarse dentro de la rotación.',
            'severidad' => 'ninguna',
            'estado' => 'activa',
            'fecha_generada' => now(),
        ]);
        return;
    }

    // Si es el primer año del ciclo, no evaluamos alertas históricas
    if ($anioActual == $anioInicioPlan) {
        return;
    }

    // Historial de la misma parcela
    $historial = DetalleRotacion::whereHas('plan', fn($q) =>
            $q->where('parcela_id', $parcela->id))
        ->where('id', '!=', $detalle->id)
        ->whereNotNull('fecha_inicio')
        ->orderBy('fecha_inicio', 'desc')
        ->get();

    // Detalle anterior
    $detalleAnterior = $historial->first(fn($d) =>
        \Carbon\Carbon::parse($d->fecha_inicio)->year < $anioActual
    );
    $cultivoAnterior = $detalleAnterior?->cultivo;

    // Cultivo repetido - alta
    if ($cultivoAnterior && $cultivoActual && $cultivoAnterior->id === $cultivoActual->id) {
        $desc = "Se detecta repetición del mismo cultivo ({$cultivoActual->nombre}) respecto al año anterior ({$cultivoAnterior->nombre}). "
            . "Por qué importa: repetir la misma especie favorece la acumulación de patógenos y el agotamiento de nutrientes. "
            . "Riesgos: menor productividad y mayores problemas sanitarios. "
            . "Recomendación: alternar con leguminosas o introducir un año de descanso.";

        AlertaRotacion::create([
            'detalle_rotacion_id' => $detalle->id,
            'tipo_alerta' => 'cultivo_repetido',
            'descripcion' => $desc,
            'severidad' => 'alta',
            'estado' => 'activa',
            'fecha_generada' => now(),
        ]);
        return;
    }

    // Misma familia botánica - alta
    if ($cultivoAnterior && $cultivoActual &&
        $cultivoAnterior->categoria && $cultivoActual->categoria &&
        $cultivoAnterior->categoria === $cultivoActual->categoria &&
        $cultivoAnterior->id !== $cultivoActual->id) {

        $familia = ucfirst($cultivoActual->categoria);
        $desc = "El cultivo {$cultivoActual->nombre} pertenece a la misma familia botánica ({$familia}) que el del año anterior ({$cultivoAnterior->nombre}). "
            . "Por qué importa: comparten enfermedades y necesidades nutricionales similares. "
            . "Riesgos: presión de plagas y agotamiento específico de nutrientes. "
            . "Recomendación: alternar con leguminosas o familias distintas.";

        AlertaRotacion::create([
            'detalle_rotacion_id' => $detalle->id,
            'tipo_alerta' => 'misma_familia',
            'descripcion' => $desc,
            'severidad' => 'alta',
            'estado' => 'activa',
            'fecha_generada' => now(),
        ]);
    }

    // Cultivos de alta demanda consecutivos - alta
    if ($cultivoAnterior && $cultivoActual &&
        strtolower($cultivoAnterior->cargaSuelo ?? '') === 'alta' &&
        strtolower($cultivoActual->cargaSuelo ?? '') === 'alta') {

        $desc = "Se detectan dos cultivos consecutivos con alta demanda nutricional ({$cultivoAnterior->nombre} → {$cultivoActual->nombre}). "
            . "Por qué importa: agotan los nutrientes del suelo y reducen su estructura. "
            . "Riesgos: pérdida de fertilidad y dependencia de fertilizantes químicos. "
            . "Recomendación: alternar con leguminosas o cultivos de menor exigencia.";

        AlertaRotacion::create([
            'detalle_rotacion_id' => $detalle->id,
            'tipo_alerta' => 'alta_demanda_consecutiva',
            'descripcion' => $desc,
            'severidad' => 'alta',
            'estado' => 'activa',
            'fecha_generada' => now(),
        ]);
    }
}

}
