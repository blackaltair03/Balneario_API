<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Balneario;
use Illuminate\Http\Request;

class BalnearioController extends Controller
{
    public function index()
    {
        $balnearios = Balneario::select('id', 'nombre', 'capacidad', 'aforo_actual')
            ->withCount(['brazaletes as verificados' => function($query){
                $query->where('status', 'activo');
            }])

            ->withCount(['brazaletes as Rechazados' => function($query){
                $query->where('status', 'rechazado');
            }])
            ->get()
            ->map(function($balneario) {
                $balneario->porcentaje_aforo = ($balneario->aforo_actual / $balneario->capacidad) *100;
                return $balneario;
            });
        return response()->json($balneario);

    }
    public function estadisticasChecador(Request $request)
    {
        $user = $request->user();

        $estadisticas = [
            'total_verificados' => $user->brazaletesVerificados()->where('status', 'activo')->count(),
            'total_rechazados' => $user->brazaletesVerificados()->where('status', 'rechzado')->count(),
            'total_escaneados' => $user->brazaletesVerificados()->count(),
        ];
        return response()->json($estadisticas);
    }
}
