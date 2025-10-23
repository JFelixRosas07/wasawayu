<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Parcela;
use App\Models\Cultivo;
use App\Models\PlanRotacion;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
{
    $user = auth()->user();

    if ($user->hasRole('Agricultor')) {
        $totalAgricultores = 1; // Solo él
        $totalParcelas = $user->parcelas()->count();
        $totalCultivos = $user->parcelas()->with('planes.detalles.cultivo')->get()
                            ->pluck('planes.*.detalles.*.cultivo')->flatten()->unique('id')->count();
        $totalRotaciones = $user->parcelas()->with('planes')->get()
                            ->pluck('planes')->flatten()->count();
    } else {
        $totalAgricultores = User::role('Agricultor')->count();
        $totalParcelas = Parcela::count();
        $totalCultivos = Cultivo::count();
        $totalRotaciones = PlanRotacion::count();
    }

    return view('home', compact(
        'totalAgricultores',
        'totalParcelas', 
        'totalCultivos',
        'totalRotaciones'
    ));
}

}