<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Persona;
use Illuminate\Http\Request;

class HistorialController extends Controller
{

    //  Busca a una persona por nombre, apellido o cédula y muestra en qué
    // retiros ha estado: cuál terminó (y con qué número) y cuáles no.
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->busqueda);
        $personas = collect();

        if (mb_strlen($busqueda) >= 3) {
            $cedula = Persona::normalizarCedula($busqueda);

            $personas = Persona::with(['inscripciones' => fn ($consulta) => $consulta->with(['retiro', 'mesa'])->latest()])
                ->where(function ($donde) use ($busqueda, $cedula) {
                    $donde->whereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ['%' . $busqueda . '%'])
                        ->orWhere('cedula', 'like', '%' . $cedula . '%');
                })
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->limit(50)
                ->get();
        }

        $totales = [
            'retiristas' => Inscripcion::where('estado', Inscripcion::TERMINO)->distinct('persona_id')->count('persona_id'),
            'personas'   => Persona::count(),
        ];

        return view('historial.index', compact('busqueda', 'personas', 'totales'));
    }
}
