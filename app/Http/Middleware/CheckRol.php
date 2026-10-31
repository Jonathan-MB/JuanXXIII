<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRol
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $usuario = $request->user();

        //  Se compara por NOMBRE de rol (Rol::ADMINISTRACION...), nunca
        // por id, para no depender del orden en que se sembraron.
        if (! $usuario || ! $usuario->tieneRol(...$roles)) {
            return redirect()->route('panel')->with('mensajeAlerta', 'No tienes permiso para entrar a esa sección.');
        }

        return $next($request);
    }
}
