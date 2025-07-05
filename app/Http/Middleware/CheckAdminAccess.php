<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use illuminate\Support\Facades\Auth;

class CheckAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::cheack || !Auth::user()->isAdmin()){
            return response()->json([
                'error' => 'Acceso no Aitorizado'
            ], 403);
        }

        return $next($request);
    }
}
