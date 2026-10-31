<?php

namespace App\Http\Controllers;

use App\Models\Retiro;

class InicioController extends Controller
{

    // Página pública: muestra el próximo retiro abierto, si lo hay
    public function index()
    {
        $retiro = Retiro::proximoAbierto();

        return view('inicio', compact('retiro'));
    }
}
