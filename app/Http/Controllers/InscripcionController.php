<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInscripcionRequest;
use App\Mail\ConfirmacionInscripcion;
use App\Models\Ajuste;
use App\Models\Inscripcion;
use App\Models\Persona;
use App\Models\Retiro;
use App\Models\RetiristaAnterior;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InscripcionController extends Controller
{

    // Edad mínima para vivir el retiro, cumplida al empezar el retiro
    private const EDAD_MINIMA = 16;



    public function create()
    {
        $retiro = Retiro::proximoAbierto();

        return view('inscripcion', compact('retiro'));
    }



    public function store(StoreInscripcionRequest $request)
    {

        $retiro = Retiro::proximoAbierto();

        if (! $retiro) {
            return redirect()->route('inscripcion')->with('error', 'Las inscripciones se cerraron mientras llenabas el formulario.');
        }

        $data = $request->validated();

        //  La edad se mide al día en que empieza el retiro, no hoy:
        // alguien que cumple 16 la semana del retiro sí puede ir.
        $edad = \Carbon\Carbon::parse($data['fecha_nacimiento'])->diffInYears($retiro->fecha_inicio);

        if ($edad < self::EDAD_MINIMA) {
            return back()->withInput()->withErrors(['fecha_nacimiento' => 'El retiro es para mayores de ' . self::EDAD_MINIMA . ' años.']);
        }

        $resultado = DB::transaction(function () use ($request, $data, $retiro) {

            //  lockForUpdate: si la misma cédula se envía dos veces a la vez,
            // la segunda espera y entonces sí ve la inscripción de la primera.
            $persona = Persona::where('cedula', $data['cedula'])->lockForUpdate()->first();

            //  Puede que nunca se haya inscrito aquí pero esté en los
            // registros de antes de la aplicación.
            $yaVivio = $persona ? $persona->yaHizoRetiro() : RetiristaAnterior::where('cedula', $data['cedula'])->exists();

            if ($yaVivio) {
                return 'Esta cédula ya vivió el retiro Juan XXIII. El retiro se vive una sola vez.';
            }

            if ($persona && $persona->inscripciones()->where('retiro_id', $retiro->id)->exists()) {
                return 'Esta cédula ya está inscrita en este retiro. Si necesitas corregir un dato, comunícate con los contactos de informes.';
            }

            $datosPersona = collect($data)->only((new Persona)->getFillable())->except('cedula')->all();

            //  La inscripción ya no pide dirección ni correo: a quien ya estaba
            // registrado se le conservan los que tenía; a uno nuevo, en blanco.
            if (! $persona) {
                $datosPersona['direccion'] = '';
            }

            // Si no asistió a un retiro anterior, se actualizan sus datos
            $persona = Persona::updateOrCreate(['cedula' => $data['cedula']], $datosPersona);

            $comprobante = $request->hasFile('comprobante')
                ? $request->file('comprobante')->store('comprobantes/retiro-' . $retiro->id)
                : null;

            $inscripcion = Inscripcion::create([
                'estado'             => Inscripcion::INSCRITO,
                'forma_pago'         => $data['forma_pago'],
                'comprobante'        => $comprobante,
                'tiene_padrino'      => $data['tiene_padrino'],
                'padrino_nombre'     => $data['tiene_padrino'] ? $data['padrino_nombre'] : null,
                'padrino_telefono'   => $data['tiene_padrino'] ? $data['padrino_telefono'] : null,
                'padrino_presente'   => $data['tiene_padrino'] ? $data['padrino_presente'] : null,
                'padrino_parroquia'  => $data['tiene_padrino'] ? $data['padrino_parroquia'] : null,
                'acepta_datos'       => true,
                'declara_requisitos' => true,
                'persona_id'         => $persona->id,
                'retiro_id'          => $retiro->id,
            ]);

            $inscripcion->contactosEmergencia()->createMany($data['contactos_emergencia']);

            return $inscripcion;
        });

        // Si la transacción devolvió texto, es el motivo del rechazo
        if (is_string($resultado)) {
            return back()->withInput()->withErrors(['cedula' => $resultado]);
        }

        //  Solo si en Ajustes se pidió mandarlo apenas se inscribe. Si el correo
        // falla, la inscripción ya quedó guardada: se anota y se sigue.
        if (Ajuste::valor(Ajuste::CORREO_AUTOMATICO) === '1' && $resultado->persona->correo) {
            try {
                Mail::to($resultado->persona->correo)->send(new ConfirmacionInscripcion($resultado));
                $resultado->update(['confirmacion_enviada' => now()]);
            } catch (\Throwable $error) {
                Log::error('No se pudo enviar la confirmación', ['inscripcion' => $resultado->id, 'error' => $error->getMessage()]);
            }
        }

        return redirect()->route('inscripcion.recibida')->with('inscripcionRecibida', [
            'nombre'         => $data['nombres'],
            'retiro'         => ($data['sexo'] === 'Femenino' ? 'Retiro de mujeres N.º ' : 'Retiro de hombres N.º ') . $retiro->numeroPara($data['sexo']),
            'fechas'         => $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year,
            'conComprobante' => (bool) $resultado->comprobante,
        ]);
    }



    public function recibida()
    {
        // Solo se llega aquí justo después de inscribirse
        if (! session()->has('inscripcionRecibida')) {
            return redirect()->route('inscripcion');
        }

        $retiro = Retiro::proximoAbierto();

        return view('inscripcionRecibida', [
            'recibida' => session('inscripcionRecibida'),
            'retiro'   => $retiro,
        ]);
    }



    //  Se consulta al pasar del paso 1, para avisar antes de que la
    // persona llene todo. Solo dice si puede o no; nunca devuelve datos.
    public function verificarCedula(string $cedula)
    {
        $retiro  = Retiro::proximoAbierto();
        $cedula  = Persona::normalizarCedula($cedula);
        $persona = Persona::where('cedula', $cedula)->first();
        $yaVivio = $persona ? $persona->yaHizoRetiro() : RetiristaAnterior::where('cedula', $cedula)->exists();

        if ($yaVivio) {
            return response()->json(['puede' => false, 'mensaje' => 'Esta cédula ya vivió el retiro Juan XXIII. El retiro se vive una sola vez.']);
        }

        if ($persona && $retiro && $persona->inscripciones()->where('retiro_id', $retiro->id)->exists()) {
            return response()->json(['puede' => false, 'mensaje' => 'Esta cédula ya está inscrita en este retiro.']);
        }

        return response()->json(['puede' => true]);
    }
}
