<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMobileAppAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }
        $user = Auth::user();

        if (!$user->isChecador()) {
            return response()->json([
                'error' => 'Acceso no autorizado',
                'message' => 'Solo los checadores pueden acceder a esta funcionalidad'
            ], 403);
        }
        //verificacion del checador tenga un balneario asigando
        if (!user->balneario_id) {
            return response()->json([
                'error' => 'Configuracion incompleta',
                'message' => 'El checador no tiene un balneario asignado'
            ], 403);
        }
        return $next($request);
    }
}
