<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Balneario;
use App\Models\User;
use App\Models\Brazalete;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function  index()
    {
        $totalUsusarios = User::count();
        $totalBalnearios = Balneario::count();
        $totalIngresos = Brazalete::where('status', 'activo')->count();
        $totalReigresos = Brazalete::where('status', 'activo')->where('fecha_verificacion', '>', now()->sudDay())
        ->count();

        return response()->json([
            'total_usuarios' => $totalUsusarios,
            'total_balnearios' => $totalBalnearios,
            'total_ingresos' => $totalIngresos,
            'total_reingresos' =>$totalReigresos,
        ]);
    }

    public function estadisticas()
    {
        $balnearios = Balneario::withCount([
            'brazaletes as ingresos' => function($query) {
                $query->where('status', 'activo');
            },
            'brazaltes as ingresos_hoy' =>function($query) {
                $query->where('status', 'activo')
                ->where('fecha_verificacion', '>', now()->startOfDay());
            }
        ])->get();
    }

    private function generarDatosGraficos()
    {
        $data = [];
        $balnearios = Balneario::all();

        foreach ($balnearios as $balneario) {
            $data[] = [
                'balneario' => $balneario->nombre,
                'ingresos' => $balneario->brazaletes()->where('status', 'activo')->count(),
                
                'capacidad' => $balneario->capacidad,
                'aforo' => $balneario->aforo_actual     
            ];
        }
        return $data;
    }
}

//Conusulta de los ultimo 7 dias del total de ventas durante el dia y hacer la comparativa 
//tipo de entradas Convenio, seguro, isste, descuentos mayores de edad niños, ingreso normal 