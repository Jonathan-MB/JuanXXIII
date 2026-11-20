<?php

namespace App\Http\Controllers;

use App\Support\Buscador;
use Illuminate\Http\Request;

class BuscadorController extends Controller
{

    //  Buscador general para todas las cuentas. Mientras se escribe, la
    // página pide solo los resultados (sin recargar).
    public function index(Request $request)
    {
        $texto      = mb_substr(trim((string) $request->q), 0, 80);
        $resultados = (new Buscador($request->user()))->buscar($texto);
        $datos      = ['texto' => $texto] + $resultados;

        if ($request->boolean('parcial')) {
            return view('buscador.resultados', $datos);
        }

        return view('buscador.index', $datos);
    }
}
