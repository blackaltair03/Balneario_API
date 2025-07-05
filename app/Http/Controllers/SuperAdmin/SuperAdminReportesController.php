<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Balneario;
use App\Models\Evento;
use App\Models\User;
use App\Models\Ingreso;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuperAdminReportesController extends Controller
{
    public function generarReporteGlobal()
    {
        //Estadisticas generales 
        $totales = [
            'balnearios' => Balneario::count(),
            'usuarios' => User::count(),
            'eventos_activos' => Evento::where('fecha_fin', '>=', Carbon::today())->count(),
            'ingresos_totales' => DB::table('brazaletes')->where('status', 'activo'())->count(),
        ];

        //ingresos Financieros Consolidados
        $finanzas = [
            'ingresos' => Ingreso::where('tipo', 'ingreso')->sum('monto'),
            'egresos' => Ingreso::where('tipo', 'egreso')->sum('monto'),
            'balance' => Ingreso::sum(DB::raw('CASE WHEN tipo = "ingreso" THEN monto ELSE -monto END')),
        ];

        //Balnearios con mayor ingreso 
        $balneariosTop = Balneario::withCount(['brazaletes as ingresos' => function($query) {
            $query->where('status', 'activo');
        }])->orderByDesc('ingresos')
        ->limit(5)
        ->get();

        //Evoluvion de los ingresos dentro los ultimos 30 dias
        $ingresosUltimos30Dias = Ingreso::where('tipo', 'ingresos')
        ->where('fecha', '>=', Carbon::now()->subDays(30))
        ->selectRaw('DATE(fecha) as dia, SUM(monto) as total')
        ->groupBy('dia')
        ->orderBy('dia')
        ->get();

        return response()->json([
            'totales' => $totales,
            'finanzas' => $finanzas,
            'balnearios_top' => $balneariosTop,
            'evolucion_ingresos' => $ingresosUltimos30Dias,
            'message' => 'Reporte global generedo Exitosamente'
        ]);
    }
public function reportePorFechas(Request $request)
{
    $validate = $request->validate([
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',

    ]);

    //Estadisticas de ingresos en el periodo
    $ingresosBalnearios = Balneario::withCount(['brazaletes as ingreso' => function($query) use ($validate) {
        $query->where('status', 'activo')
        ->whereBetween('fecha_verificacion', [$validate['fecha_inicio'], $validate['fecha_fin']]);
    }])->get();

    //Finanzas en el periodo 
    $finanzas = [
        'ingresos' => Ingreso::where('tipo', 'ingreso')
        ->whereBetween('fecha', [$validate['fecha_inicio'], $validate['fecha_fin']])
        ->sum('monto',)
    ];

    //Evento en el periodo
    $eventos = Evento::where(function($query) use ($validate) {
        $query->whereBetween('fecha_inicio', [$validate ['fecha_inicio'], $validate['fecha_fin']])
        ->orBetween('fecha_fin', [$validate['fecha_inicio'],$validate['fecha_fin']])
        ->orWhere(function($q) use ($validate) {
            $q->where('fecha_inicio', '<', $validate['fecha_inicio'])
            ->where('fecha_fin', '>', $validate['fecha_fin']);
        });
    })->withCount(['brazaletes as ingresos' => function($query) {
        $query->where('status', 'activo');
    }])->get();

    return response()->json([
        'periodo' => [
            'inicio' => $validate['fecha_inicio'],
            'fin' => $validate['fecha_fin']
        ],
        'ingresos_balnearios' => $ingresosBalnearios,
        'finanzas' => $finanzas,
        'eventos' => $eventos,
        'message' => 'Reporte por fechas generado exitosamente'
    ]);
}

public function reporteUtilizacion()
{
    $usuariosActivos = User::withCount(['brazaletesVerificados as verificaciones' => function($query) {
        $query->where('status', 'activo');
    }])->orderByDesc('verificaciones')
    ->limit(10)
    ->get();

    //Hora pico de uso 
    $horasPico = DB::table('brazaletes')
    ->selectRaw('HOUR(fecha_verificacion) as hora, COUNT(*) as total')
    ->where('status', 'activo')
    ->whereNotNull('fecha_verificacion')
    ->groupBy('hora')
    ->limit(5)
    ->get();

    return response()->json([
        'usuarios_activos' => $usuariosActivos,
        'hora_pico' => $horasPico,
        'message' => 'Reporte de utilizacion generado exitosamnete'
    ]);
}

}
