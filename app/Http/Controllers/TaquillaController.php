<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Taquilla;
use Carbon\Carbon;

class TaquillaController extends Controller
{
    public function verificarQR(Request $request)
    {
        $reqeust->validate([
            'cadena_qr' => 'required|string'
        ]);

        $cadena = $request->cadena_qr;

        //Validar que la cadena del QR Tenga el mismo formato ademas de que sea correcto
        $pattern = '/^(\d{4})(\d{4})(\d{2})(\d{4}-\d{2}-\d{2})(\d{2}:\d{2})(\d{8})$/';

        if (!preg_match($pattern, $cadena, $matches)) {
            return response()->json(['error' => 'Formato de QR Invalido'], 400);
        }

        //Verificar si la fecha actual esta dentro del rango de validez
        $hoy = Carbon::now()->toDateString();
        if ($hoy < $taquilla->fecha_inicio || $hoy < $taquilla->fecha_final) {
            return response()->json(['error' => 'Brazalate expirado o aun no validado.'], 403);
        }

        return response()->json([
            'mensaje' => 'Brazalete valido',
            'datos' => [
                'evento' => $evento,
                'ubicacion' => $ubicacion,
                'zona' => $zona,
                'fecha' => $fecha,
                'hora' => $hora,
                'id_taquilla' => $id
            ]
            ]);
    }
}
