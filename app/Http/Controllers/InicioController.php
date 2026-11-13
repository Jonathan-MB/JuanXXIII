<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Retiro;

class InicioController extends Controller
{

    //  Página pública: el próximo retiro abierto, si lo hay, y la
    // invitación al rosario (sin enlace no sale)
    public function index()
    {
        $retiro = Retiro::proximoAbierto();

        $enlace  = Ajuste::valor(Ajuste::ROSARIO_ENLACE);
        $pedidos = (string) Ajuste::valor(Ajuste::ROSARIO_PEDIDOS);

        $rosario = $enlace ? (object) [
            'enlace'       => $enlace,
            'dias'         => Ajuste::valor(Ajuste::ROSARIO_DIAS),
            'hora'         => Ajuste::valor(Ajuste::ROSARIO_HORA),
            'pedidos'      => $pedidos,

            // Un celular se lee de a 3, 3 y 4 cifras: "313 644 1569"
            'pedidosTexto' => strlen($pedidos) === 10
                ? substr($pedidos, 0, 3) . ' ' . substr($pedidos, 3, 3) . ' ' . substr($pedidos, 6)
                : $pedidos,
        ] : null;

        return view('inicio', compact('retiro', 'rosario'));
    }
}
