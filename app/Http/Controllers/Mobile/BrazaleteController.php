<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Brazalete;
use App\Models\Balneario;
use Illuminate\Http\Request;

class BrazaleteController extends Controller
{
    public function verificar(Request $request)
    {
        $request->validate([
            'codigo_qr' => 'required|string',
        ]);

        $codigo = $request->codigo_qr;

        // Ajusta el delimitador según tu formato real de QR
        $partes = explode('-', $codigo);

        $balneario_id = $partes[0] ?? null;
        $evento_id = $partes[1] ?? null;
        $codigo_unico = $partes[2] ?? null;

        $brazalete = Brazalete::where('codigo_qr', $codigo_unico)
            ->where('balneario_id', $balneario_id)
            ->where('evento_id', $evento_id)
            ->first();

        if (!$brazalete) {
            return response()->json(['error' => 'Brazalete no encontrado'], 404);
        }

        // Actualización del estado y registro del checador
        $brazalete->update([
            'status' => 'activo',
            'checador_id' => $request->user()->id,
            'fecha_verificacion' => now(),
        ]);

        // Aumento del aforo
        $balneario = Balneario::find($balneario_id);
        $balneario->increment('aforo_actual');

        return response()->json([
            'message' => 'Brazalete verificado correctamente',
            'brazalete' => $brazalete,
            'aforo_actual' => $balneario->aforo_actual,
            'porcentaje_aforo' => ($balneario->aforo_actual / $balneario->capacidad) * 100,
        ]);
    }

    public function buscar(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'evento_id' => 'required|exists:eventos,id',
        ]);

        $brazaletes = Brazalete::where('evento_id', $request->evento_id)
            ->where(function ($query) use ($request) {
                $query->where('codigo_qr', 'like', '%' . $request->query . '%')
                    ->orWhereHas('balneario', function ($q) use ($request) {
                        $q->where('nombre', 'like', '%' . $request->query . '%');
                    });
            })
            ->with('balneario')
            ->get();

        return response()->json($brazaletes);
    }
}
