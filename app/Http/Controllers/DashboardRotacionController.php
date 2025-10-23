<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardRotacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // permitir acceso a los tres roles
        $this->middleware('role:Administrador|TecnicoAgronomo|Agricultor');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // si el usuario es agricultor, solo ve sus propios datos
        if ($user->hasRole('Agricultor')) {
            $agricultores = User::role('Agricultor')
                ->where('id', $user->id)
                ->with([
                    'parcelas' => function ($q) {
                        $q->with([
                            'planes' => function ($p) {
                                $p->with(['detalles.cultivo'])
                                  ->orderBy('created_at', 'desc');
                            }
                        ]);
                    }
                ])
                ->get();

            // en modo agricultor, no se muestra el selector de agricultores
            $listaAgricultores = collect();
            $agricultorId = $user->id;
        } 
        else {
            // si es admin o tecnico, puede ver todo y filtrar por agricultor
            $agricultorId = $request->get('agricultor_id');

            $listaAgricultores = User::role('Agricultor')
                ->orderBy('name')
                ->get(['id', 'name']);

            $agricultores = collect();

            if ($agricultorId) {
                $agricultores = User::role('Agricultor')
                    ->where('id', $agricultorId)
                    ->with([
                        'parcelas' => function ($q) {
                            $q->with([
                                'planes' => function ($p) {
                                    $p->with(['detalles.cultivo'])
                                      ->orderBy('created_at', 'desc');
                                }
                            ]);
                        }
                    ])
                    ->get();
            }
        }

        return view('rotaciones.dashboard', compact('agricultores', 'listaAgricultores', 'agricultorId'));
    }
}
