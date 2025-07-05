<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Balneario;
use App\Models\Brazalete;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::withCount(['brazaletes as total_ingresos'=> function($query) {
            $query->where('status', 'activo');
        }])->orderBy('fecha_inicio', 'desc')->get();

        return response()->json([
            'data' => $eventos,
            'message' => 'Lista de Eventos obtenida exitosamente'
        ]);
    }

    public function store(Request $request)

    {
        $validate = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $evento = Evento::create($validate);

        return response()->json([
            'data' => $evento,
            'message' => 'Evento creado exitosamente',
        ], 201);
    }
    public function evento(Evento $evento)
    {
        $evento->load(['brazaletes.balneario', 'brazaletes.checador']);

        //Estadisticas/metricas del balneario
        $estadisticas = Balnerio::withCount(['brazaletes as ingresos' => function($query) use ($evento) {
            $query->where('evento_id', $evento->id)
            ->where('status', 'activo');
        }])->get();

        return response()->json([
            'data' => $evento,
            'estadisticas' => $estadisticas,
            'message' => 'Detalles del evento obtenidos exitosamente'
        ]);
    }

    public function update(Request $request, Evento $evento)
    {
        $validate = $request->validate([
            'nombre' => 'somtimes|string|max:255',
            'fecha_inicio' => 'sometimes|date',
            'fecha_fin' => 'sometimes|date|after_or_equal:fecha_inicio',
        ]);

        $evento->update($validate);
        return response()->json([
            'data' => $evento,
            'message' => 'Evento actualizado Exitosamente'
        ]);
    }

    public function destroy(Evento $evento)
    {
        //Verifiacion que no tenga los brazaletes asociados
        if ($evento->brazaletes()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el evento porque tiene brazaletes asociados'
            ], 422);
        }
        $evento->delete();
        return response()->json([
            'message' => 'Evento eliminado Exitosamente'
        ], 204);
    }
    public function estadisticas(Evento $evento)
    {
        $ingresosPorDia = Brazalete::where('evento_id', $evento->id)
        ->where('status', 'activo')
        ->selectRaw('DATE(fecha_verificaciom) as fecha, COUNT(*) as total')
        ->groupBy('fecha')
        ->orderBy('fecha')
        ->get();

        $ingresosPorBalneario = Balneario::withCount(['brazaletes as ingresos' => function($query) use ($evento) {
            $query->where('evento_id', $evento->id)
            ->where('status', 'activo');
        }])->get();

        return response()->json([
            'ingresos_por_dia' => $ingresosPorDia,
            'ingresos_por_balneario' => $ingresosPorBalneario,
            'total_ingresos' => $evento->brazaletes()->where('status', 'activo')->count(),
            'message' => 'Estadisticas del evento obtenidas exitosamente'
        ]);
    }
}
