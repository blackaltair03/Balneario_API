<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Balneario;
use App\Models\Ingreso;
use App\Models\Brazalete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BalnearioAdminController extends Controller
{
    public function index()
    {
        //Muestra del balneario del administrador actual 
        $balneario = auth()->user()->balneario;

        if (!$balneario) {
            return response()->json([
                'error' => 'No tienes un balneario registrado'
            ], 403);
        }

        return response()->json([
            'data' => $balneario->load(['servicios', 'usuarios' => function($query) {
                $query->where('rol_id', 3);
            }])
        ]);
    }

    public function show(Balneario $balneario)
    {
        //verificar que el admin solo pueda ver su propio balneario
        if (auth()->user()->balneario_id !== $balneario->id) {
            return response()->json([
                'error' => 'No Autorizado para acceder a este balneario'
            ], 403);
        }
        $balneario->load([
            'servicios',
            'brazaletes' => function($query) {
                $query->where('status', 'activo')
                ->where('fecha_verificacion', '>=', Carbon::today());
            },
            'brazalete.checador'
        ]);

        return response()->json([
            'data' => $balneario,
            'estadisticas' => $this->calcularEstadisticas($balneario)
        ]);
    }

    public function reporteIngresos()
    {
        $balnearioId = auth()->user()->balneario_id;

        $ingresos = Ingreso::where('balneario_id', $balnearioId)
        ->where('tipo', 'ingreso')
        ->select(
            DB::raw('SUM(monto) as total'),
            DB::raw('DATE(fecha) as fecha'),
            'concepto'
        )
        ->groupBy('fecha', 'concepto')
        ->orderBy('fecha', 'desc')
        ->get();

        return response()->json([
            'data' => $ingresos,
            'total' => $ingresos->sum('total')
        ]);
    }

    public function reporteEgresos()
    {
        $balnearioId = auth()->user()->balneario_id;

        $egresos = Ingreso::where('balneario_id', $balnearioId)
        ->where('tipo', 'egreso')
        ->select(
            DB::raw('SUM(monto) as total'),
            DB::raw('DATE(fecha) as fecha'),
            'concepto'
        )
        ->groupBy('fecha', 'concepto')
        ->orderBy('fecha', 'desc')
        ->get();

        return response()->json([
            'data' => $egresos,
            'total' => $egresos->sum('total')
        ]);
    }

    private function calcularEstadisticas(Balneario $balneario)
    {
        return [
            'total_ingresos' => $balneario->brazaletes()->where('status', 'activo')->count(),
            'ingresos_hoy' => $balneario->brazaletes()
                ->where('status', 'activo')
                ->whereDate('fecha_verificacion', Carbon::today())
                ->count(),
            'ingresos_semana' => $balneario->brazaletes()
                ->where('status', 'activo')
                ->whereBetween('fecha_verificacion', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),
            'porcentaje_aforo' => round(($balneario->aforo_actual / $balneario->capacidad) * 100, 2),
            'top_checadores' => $this->obtenerTopChecadores($balneario),
            'ingresos_por_evento' => $this->ingresosPorEvento($balneario)
        ];
    }

    private function obtenerTopChecadores(Balneario $balneario)
    {
        return $balneario->brazaletes()
            ->select('checador_id', DB::raw('count(*) as total'))
            ->whereNotNull('checador_id')
            ->with('checador:id,nombre_completo')
            ->groupBy('checador_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'checador' => $item->checador->nombre_completo,
                    'total' => $item->total
                ];
            });
    }

    private function ingresosPorEvento(Balneario $balneario)
    {
        return $balneario->brazaletes()
            ->select('evento_id', DB::raw('count(*) as total'))
            ->where('status', 'activo')
            ->with('evento:id,nombre')
            ->groupBy('evento_id')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'evento' => $item->evento->nombre,
                    'total' => $item->total
                ];
            });
    }
}