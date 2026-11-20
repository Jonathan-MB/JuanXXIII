<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Retiro;
use Illuminate\Http\Request;

class DirectivoController extends Controller
{

    //  Lista de inscritos del retiro actual, solo para consultar: sin
    // pagos, sin fichas y sin editar. Quien no asistió o abandonó no sale.
    public function index(Request $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return view('directivos.index', ['retiro' => null]);
        }

        $grupo    = $request->sexo === 'mujeres' ? 'mujeres' : 'hombres';
        $busqueda = trim((string) $request->busqueda);

        $consulta = Inscripcion::with(['persona', 'mesa'])
            ->select('inscripcions.*')
            ->where('retiro_id', $retiro->id)
            ->whereNotIn('inscripcions.estado', Inscripcion::ESTADOS_FUERA)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->where('personas.sexo', $grupo === 'mujeres' ? 'Femenino' : 'Masculino')
            ->orderBy('personas.nombres')
            ->orderBy('personas.apellidos');

        if ($busqueda !== '') {
            $consulta->whereRaw("CONCAT(personas.nombres, ' ', personas.apellidos) LIKE ?", ['%' . $busqueda . '%']);
        }

        $inscritos = $consulta->get();

        $conteos = Inscripcion::where('retiro_id', $retiro->id)
            ->whereNotIn('inscripcions.estado', Inscripcion::ESTADOS_FUERA)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->selectRaw("SUM(personas.sexo = 'Masculino') AS hombres, SUM(personas.sexo = 'Femenino') AS mujeres")
            ->first();

        return view('directivos.index', compact('retiro', 'inscritos', 'grupo', 'busqueda', 'conteos'));
    }
}
