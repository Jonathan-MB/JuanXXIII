<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorregirDatosRequest;
use App\Models\Inscripcion;
use App\Models\Mesa;
use App\Models\Retiro;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LlegadaController extends Controller
{

    public function index(Request $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return view('llegada.index', ['retiro' => null]);
        }

        $sexo     = $request->sexo === 'mujeres' ? 'Femenino' : 'Masculino';
        $busqueda = trim((string) $request->busqueda);

        $mesas = $retiro->mesas()
            ->where('sexo', $sexo)
            ->with(['inscripciones.persona'])
            ->orderBy('numero')
            ->get();

        // Los de este grupo que siguen en la lista (no los que no asistieron)
        $grupo = Inscripcion::with(['persona', 'retiro', 'mesa'])
            ->select('inscripcions.*')
            ->withSum('pagos', 'valor')
            ->where('retiro_id', $retiro->id)
            ->whereNotIn('inscripcions.estado', Inscripcion::ESTADOS_FUERA)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->where('personas.sexo', $sexo)
            ->orderBy('personas.apellidos')
            ->get();

        $encontrados = collect();

        if ($busqueda !== '') {
            $texto       = mb_strtolower($busqueda);
            $cedula      = preg_replace('/[\s.\-]/', '', $busqueda);
            $encontrados = $grupo->filter(function ($inscrito) use ($texto, $cedula) {
                return str_contains(mb_strtolower($inscrito->persona->nombre_completo), $texto)
                    || ($cedula !== '' && str_contains($inscrito->persona->cedula, strtoupper($cedula)));
            })->take(8)->values();
        }

        $llegaron  = $grupo->whereNotNull('llegada_en')->count();
        $faltan    = $grupo->whereNull('llegada_en')->values();
        $sinPuesto = $grupo->whereNull('mesa_id')->values();

        // Se manda como inscritosGrupo: en la vista, $grupo es "hombres" o "mujeres"
        return view('llegada.index', array_merge(
            compact('retiro', 'sexo', 'busqueda', 'mesas', 'encontrados', 'llegaron', 'faltan', 'sinPuesto'),
            ['inscritosGrupo' => $grupo]
        ));
    }



    //  Solo marca que llegó. El puesto NO se da por orden de llegada:
    // la mesa y la silla se escriben a mano aparte.
    public function marcar(Inscripcion $inscripcion)
    {
        if ($inscripcion->estaFuera()) {
            return back()->with('error', 'Esta persona está marcada como que no asistió o abandonó. Devuélvela a la lista desde su ficha.');
        }

        if ($inscripcion->llegada_en) {
            return back()->with('mensajeAlerta', $inscripcion->persona->nombres . ' ya había llegado.');
        }

        $inscripcion->update([
            'estado'     => Inscripcion::LLEGO,
            'llegada_en' => now(),
        ]);

        return back()->with('mensajeCreado', $inscripcion->persona->nombre_completo . ' llegó.');
    }



    //  La mesa y la silla se escriben a mano. Los puestos son fijos:
    // cambiar o quitar el de una persona nunca mueve a las demás.
    // Si la mesa no existe todavía para este grupo, se crea.
    public function asignarPuesto(Request $request, Inscripcion $inscripcion)
    {
        $request->validate([
            'mesa'  => ['nullable', 'required_with:silla', 'integer', 'between:1,99'],
            'silla' => ['nullable', 'required_with:mesa', 'integer', 'between:1,' . Mesa::MAXIMO_SILLAS],
        ], [
            'mesa.required_with'  => 'Escribe también el número de mesa.',
            'silla.required_with' => 'Escribe también el número de silla.',
            'mesa.between'        => 'El número de mesa va de 1 a 99.',
            'silla.between'       => 'El número de silla va de 1 a ' . Mesa::MAXIMO_SILLAS . '.',
        ]);

        $nombre = $inscripcion->persona->nombre_completo;

        // Las dos casillas vacías quitan el puesto
        if (! $request->filled('mesa')) {
            $inscripcion->update(['mesa_id' => null, 'silla' => null]);

            return back()->with('mensajeActualizado', $nombre . ' quedó sin puesto.');
        }

        $numeroMesa = (int) $request->mesa;
        $silla      = (int) $request->silla;

        try {
            $resultado = DB::transaction(function () use ($inscripcion, $numeroMesa, $silla) {

                //  lockForUpdate: si dos personas escriben el mismo puesto a
                // la vez, la segunda espera y ve que ya está ocupado.
                $mesa = Mesa::where('retiro_id', $inscripcion->retiro_id)
                    ->where('sexo', $inscripcion->persona->sexo)
                    ->where('numero', $numeroMesa)
                    ->lockForUpdate()
                    ->first();

                if (! $mesa) {
                    $mesa = Mesa::create([
                        'retiro_id'       => $inscripcion->retiro_id,
                        'sexo'            => $inscripcion->persona->sexo,
                        'numero'          => $numeroMesa,
                        'cantidad_sillas' => $silla,
                    ]);
                }

                $ocupante = Inscripcion::with('persona')
                    ->where('mesa_id', $mesa->id)
                    ->where('silla', $silla)
                    ->where('id', '!=', $inscripcion->id)
                    ->first();

                if ($ocupante) {
                    return 'La mesa ' . $numeroMesa . ', silla ' . $silla . ' ya es de ' . $ocupante->persona->nombre_completo . '.';
                }

                // La mesa crece hasta la silla más alta que se use
                if ($silla > $mesa->cantidad_sillas) {
                    $mesa->update(['cantidad_sillas' => $silla]);
                }

                $inscripcion->update(['mesa_id' => $mesa->id, 'silla' => $silla]);

                return null;
            });
        } catch (QueryException $error) {
            // El índice único (mesa, silla) atajó un choque de último momento
            return back()->with('error', 'Ese puesto se acaba de ocupar. Intenta de nuevo.');
        }

        if ($resultado) {
            return back()->with('error', $resultado);
        }

        return back()->with('mensajeActualizado', $nombre . ' → Mesa ' . $numeroMesa . ' · Silla ' . $silla);
    }



    //  Corrección rápida desde la lista de puestos: cuando la persona
    // escribió mal su nombre o su cédula en la hoja de la mesa.
    public function corregirDatos(CorregirDatosRequest $request, Inscripcion $inscripcion)
    {
        $persona = $inscripcion->persona;

        // Cargar datos sin guardar
        $persona->fill($request->validated());

        // No hubo cambios
        if (! $persona->isDirty()) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $persona->save();

        return back()->with('mensajeActualizado', 'Datos corregidos: ' . $persona->nombre_completo . ' · CC ' . $persona->cedula);
    }



    // Por si se marcó la llegada de la persona equivocada. El puesto se conserva.
    public function deshacer(Inscripcion $inscripcion)
    {
        $inscripcion->update([
            'estado'     => Inscripcion::CONFIRMADO,
            'llegada_en' => null,
        ]);

        return back()->with('mensajeActualizado', 'Se deshizo la llegada de ' . $inscripcion->persona->nombres . '.');
    }
}
