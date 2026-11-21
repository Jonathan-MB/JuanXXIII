<?php

namespace App\Http\Controllers;

use App\Exceptions\PuestoOcupado;
use App\Http\Requests\CorregirDatosRequest;
use App\Models\ContactoEmergencia;
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

        $sexo = $request->sexo === 'mujeres' ? 'Femenino' : 'Masculino';

        $mesas = $retiro->mesas()
            ->where('sexo', $sexo)
            ->with(['inscripciones.persona'])
            ->orderBy('numero')
            ->get();

        //  Los de este grupo, también los que se fueron (salen tachados); no
        // los que no asistieron. La búsqueda se hace en la misma página.
        $grupo = Inscripcion::with(['persona', 'retiro', 'mesa', 'contactosEmergencia'])
            ->select('inscripcions.*')
            ->withSum('pagos', 'valor')
            ->where('retiro_id', $retiro->id)
            ->where('inscripcions.estado', '!=', Inscripcion::NO_ASISTIO)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->where('personas.sexo', $sexo)
            ->orderBy('personas.nombres')
            ->orderBy('personas.apellidos')
            ->get();

        //  Quien tiene mesa y silla llegó; quien no, falta por llegar
        $enLista  = $grupo->where('estado', '!=', Inscripcion::ABANDONO);
        $llegaron = $enLista->whereNotNull('mesa_id')->count();
        $faltan   = $enLista->whereNull('mesa_id')->count();
        $seFueron = $grupo->where('estado', Inscripcion::ABANDONO)->count();

        //  El último día aparece el botón para marcar que terminaron: cuántos
        // de los dos grupos tienen puesto y todavía no están marcados
        $esUltimoDia  = now()->gte($retiro->fecha_fin->copy()->startOfDay());
        $porTerminar  = $esUltimoDia
            ? $retiro->inscripciones()
                ->whereNotNull('mesa_id')
                ->whereNotNull('silla')
                ->whereNotIn('estado', [...Inscripcion::ESTADOS_FUERA, Inscripcion::TERMINO])
                ->count()
            : 0;

        // Se manda como inscritosGrupo: en la vista, $grupo es "hombres" o "mujeres"
        return view('llegada.index', array_merge(
            compact('retiro', 'sexo', 'mesas', 'llegaron', 'faltan', 'seFueron', 'esUltimoDia', 'porTerminar'),
            ['inscritosGrupo' => $grupo]
        ));
    }



    //  «Se fue»: marca que abandonó el retiro o, si ya lo estaba, lo
    // devuelve (volvió). Quien se fue conserva su silla, que queda vacía:
    // las sillas no se le dan a nadie más.
    public function seFue(Request $request, Inscripcion $inscripcion)
    {
        $seFue = $inscripcion->estado !== Inscripcion::ABANDONO;

        if ($seFue) {
            $inscripcion->update(['estado' => Inscripcion::ABANDONO]);
        } else {
            $inscripcion->update(['estado' => $inscripcion->mesa_id ? Inscripcion::LLEGO : Inscripcion::CONFIRMADO]);
        }

        $nombre  = $inscripcion->persona->nombre_completo;
        $mensaje = $seFue ? $nombre . ' se fue: su silla queda vacía.' : $nombre . ' volvió a la lista.';

        if ($request->expectsJson()) {
            return response()->json(['seFue' => $seFue, 'mensaje' => $mensaje]);
        }

        return back()->with('mensajeActualizado', $mensaje);
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
        $mesa   = $request->filled('mesa') ? (int) $request->mesa : null;
        $silla  = $request->filled('silla') ? (int) $request->silla : null;

        try {
            DB::transaction(fn () => $this->ponerPuesto($inscripcion, $mesa, $silla));
        } catch (PuestoOcupado $ocupado) {
            return back()->with('error', $ocupado->getMessage());
        }

        return back()->with('mensajeActualizado', $mesa ? $nombre . ' → Mesa ' . $mesa . ' · Silla ' . $silla : $nombre . ' quedó sin puesto.');
    }



    //  Pone (o quita, con la mesa vacía) el puesto de una persona. Va dentro
    // de una transacción: si el puesto ya es de otra persona, lanza
    // PuestoOcupado y la transacción se deshace entera.
    //  Tener mesa y silla es haber llegado: al ponerlas queda como que llegó
    // y al quitarlas vuelve a faltar por llegar (quien se fue sigue igual).
    private function ponerPuesto(Inscripcion $inscripcion, ?int $numeroMesa, ?int $silla): void
    {
        if (! $numeroMesa) {
            $inscripcion->update(['mesa_id' => null, 'silla' => null] + ($inscripcion->estado === Inscripcion::LLEGO
                ? ['estado' => Inscripcion::CONFIRMADO, 'llegada_en' => null]
                : []));

            return;
        }

        //  lockForUpdate: si dos personas escriben el mismo puesto a la vez,
        // la segunda espera y ve que ya está ocupado.
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
            throw new PuestoOcupado($ocupante->estado === Inscripcion::ABANDONO
                ? 'La mesa ' . $numeroMesa . ', silla ' . $silla . ' era de ' . $ocupante->persona->nombre_completo . ', que se fue: esa silla queda vacía.'
                : 'La mesa ' . $numeroMesa . ', silla ' . $silla . ' ya es de ' . $ocupante->persona->nombre_completo . '.');
        }

        // La mesa crece hasta la silla más alta que se use
        if ($silla > $mesa->cantidad_sillas) {
            $mesa->update(['cantidad_sillas' => $silla]);
        }

        $llego = in_array($inscripcion->estado, [Inscripcion::INSCRITO, Inscripcion::CONFIRMADO], true)
            ? ['estado' => Inscripcion::LLEGO, 'llegada_en' => $inscripcion->llegada_en ?? now()]
            : [];

        try {
            $inscripcion->update(['mesa_id' => $mesa->id, 'silla' => $silla] + $llego);
        } catch (QueryException $choque) {
            // El índice único (mesa, silla) atajó un choque de último momento
            throw new PuestoOcupado('Ese puesto se acaba de ocupar. Intenta de nuevo.');
        }
    }



    //  La fila de cada invitado: nombres, apellidos, documento, celular,
    // fecha de nacimiento, talla, ciudad, barrio, los dos contactos de
    // emergencia y la mesa y la silla, todo
    // con un solo «Guardar». Desde la página llega por fetch y responde JSON:
    // así no se recarga ni se pierde la búsqueda. Si el puesto está ocupado,
    // no se guarda nada de la fila.
    public function corregirDatos(CorregirDatosRequest $request, Inscripcion $inscripcion)
    {
        $persona = $inscripcion->persona;
        $persona->fill($request->safe()->only(['nombres', 'apellidos', 'cedula', 'fecha_nacimiento', 'celular', 'ciudad', 'barrio', 'talla_camiseta']));

        //  Los contactos se cambian en su sitio: así no se pierden el fijo
        // ni el correo que la persona dio al inscribirse. Uno nuevo del todo
        // vacío no se crea.
        $existentes = $inscripcion->contactosEmergencia()->orderBy('id')->get()->values();
        $contactos  = collect($request->validated('contactos'))
            ->map(fn ($datos, $i) => ($existentes[$i] ?? new ContactoEmergencia())->fill($datos))
            ->reject(fn ($contacto) => ! $contacto->exists && $contacto->nombre === '' && $contacto->celular === '');

        $mesa         = $request->filled('mesa') ? (int) $request->mesa : null;
        $silla        = $request->filled('silla') ? (int) $request->silla : null;
        $sillaAntes   = $inscripcion->silla !== null ? (int) $inscripcion->silla : null;
        $cambiaPuesto = $mesa !== $inscripcion->mesa?->numero || $silla !== $sillaAntes;

        $cambianContactos = $contactos->contains(fn ($contacto) => ! $contacto->exists || $contacto->isDirty());

        if (! $persona->isDirty() && ! $cambianContactos && ! $cambiaPuesto) {
            return $this->respuestaFila($request, $inscripcion, 'No había cambios.', 'mensajeAlerta');
        }

        try {
            DB::transaction(function () use ($persona, $inscripcion, $contactos, $cambiaPuesto, $mesa, $silla) {
                $persona->save();

                foreach ($contactos as $contacto) {
                    $contacto->inscripcion_id = $inscripcion->id;
                    $contacto->save();
                }

                if ($cambiaPuesto) {
                    $this->ponerPuesto($inscripcion, $mesa, $silla);
                }
            });
        } catch (PuestoOcupado $ocupado) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $ocupado->getMessage(), 'errors' => ['mesa' => [$ocupado->getMessage()]]], 422);
            }

            return back()->withInput()->with('error', $ocupado->getMessage());
        }

        return $this->respuestaFila($request, $inscripcion->refresh(), 'Guardado: ' . $persona->nombre_completo . '.', 'mensajeActualizado');
    }



    //  Lo que necesita la fila para ponerse al día sin recargar la página
    private function respuestaFila(Request $request, Inscripcion $inscripcion, string $mensaje, string $tipo)
    {
        if (! $request->expectsJson()) {
            return back()->with($tipo, $mensaje);
        }

        $persona = $inscripcion->persona->refresh();

        return response()->json([
            'mensaje' => $mensaje,
            'edad'    => (int) $persona->fecha_nacimiento->diffInYears($inscripcion->retiro->fecha_inicio),
            'buscar'  => mb_strtolower($persona->nombre_completo . ' ' . $persona->cedula . ' ' . $inscripcion->padrino_nombre),
            'puesto'  => $inscripcion->mesa_id !== null,
        ]);
    }



    //  El último día: quien tiene mesa y silla terminó el retiro (de los
    // dos grupos). Con eso recibe carta, entra al historial y ya no se
    // puede volver a inscribir. Quien abandonó o no asistió no cuenta.
    public function terminar()
    {
        $retiro = Retiro::actual();

        if (! $retiro || now()->lt($retiro->fecha_fin->copy()->startOfDay())) {
            return back()->with('error', 'Esto se hace el último día del retiro.');
        }

        $terminaron = $retiro->inscripciones()
            ->whereNotNull('mesa_id')
            ->whereNotNull('silla')
            ->whereNotIn('estado', [...Inscripcion::ESTADOS_FUERA, Inscripcion::TERMINO])
            ->update(['estado' => Inscripcion::TERMINO]);

        if (! $terminaron) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        return back()->with('mensajeActualizado', $terminaron . ' ' . ($terminaron === 1 ? 'persona quedó' : 'personas quedaron') . ' como que terminaron el retiro.');
    }
}
