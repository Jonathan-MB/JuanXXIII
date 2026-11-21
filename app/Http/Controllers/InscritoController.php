<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInscritoRequest;
use App\Mail\ConfirmacionInscripcion;
use App\Models\Inscripcion;
use App\Models\Retiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InscritoController extends Controller
{

    public function index(Request $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return view('inscritos.index', ['retiro' => null]);
        }

        // Pestañas: hombres, mujeres, o quienes no asistieron / abandonaron
        $pestana  = in_array($request->pestana, ['hombres', 'mujeres', 'fuera'], true) ? $request->pestana : 'hombres';
        $busqueda = trim((string) $request->busqueda);
        $pago     = in_array($request->pago, ['sin_pago', 'abono', 'completo'], true) ? $request->pago : null;

        //  select() va antes de withSum(): si va después, borra la suma
        $consulta = Inscripcion::with(['persona', 'retiro'])
            ->select('inscripcions.*')
            ->withSum('pagos', 'valor')
            ->where('retiro_id', $retiro->id)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->orderBy('personas.nombres')
            ->orderBy('personas.apellidos');

        if ($pestana === 'fuera') {
            $consulta->whereIn('inscripcions.estado', Inscripcion::ESTADOS_FUERA);
        } else {
            $consulta->whereNotIn('inscripcions.estado', Inscripcion::ESTADOS_FUERA)
                ->where('personas.sexo', $pestana === 'mujeres' ? 'Femenino' : 'Masculino');
        }

        //  Se busca por nombre, apellido o cédula. La cédula se compara
        // sin puntos ni espacios, igual que se guardó.
        if ($busqueda !== '') {
            $cedula = preg_replace('/[\s.\-]/', '', $busqueda);
            $consulta->where(function ($donde) use ($busqueda, $cedula) {
                $donde->where('personas.nombres', 'like', '%' . $busqueda . '%')
                    ->orWhere('personas.apellidos', 'like', '%' . $busqueda . '%')
                    ->orWhereRaw("CONCAT(personas.nombres, ' ', personas.apellidos) LIKE ?", ['%' . $busqueda . '%'])
                    ->orWhere('personas.cedula', 'like', '%' . $cedula . '%');
            });
        }

        $inscritos = $consulta->get();

        // El estado del pago se calcula contra la ofrenda, por eso se filtra después
        if ($pago) {
            $inscritos = $inscritos->filter(fn ($inscrito) => $inscrito->estado_pago === $pago)->values();
        }

        $conteos = $this->conteos($retiro);

        return view('inscritos.index', compact('retiro', 'inscritos', 'pestana', 'busqueda', 'pago', 'conteos'));
    }



    public function show(Inscripcion $inscripcion)
    {
        $inscripcion->load(['persona', 'retiro', 'mesa', 'contactosEmergencia', 'pagos.usuario']);

        return view('inscritos.ficha', compact('inscripcion'));
    }



    public function edit(Inscripcion $inscripcion)
    {
        $inscripcion->load(['persona', 'retiro.cuentasPago', 'contactosEmergencia']);

        return view('inscritos.formulario', compact('inscripcion'));
    }



    public function update(UpdateInscritoRequest $request, Inscripcion $inscripcion)
    {
        $data = $request->validated();

        // PATCH sin data
        if (empty($data)) {
            return back()->with('mensajeAlerta', 'Sin datos');
        }

        $persona = $inscripcion->persona;

        //  Lo que se deja vacío se guarda en blanco (la EPS y los contactos
        // no admiten nulo en la base), y un contacto del todo vacío no se guarda
        $data['eps']                  = (string) ($data['eps'] ?? '');
        $data['contactos_emergencia'] = collect($data['contactos_emergencia'] ?? [])
            ->map(fn ($contacto) => [
                'nombre'        => (string) ($contacto['nombre'] ?? ''),
                'celular'       => (string) ($contacto['celular'] ?? ''),
                'telefono_fijo' => ($contacto['telefono_fijo'] ?? null) ?: null,
                'correo'        => ($contacto['correo'] ?? null) ?: null,
                'parentesco'    => (string) ($contacto['parentesco'] ?? ''),
            ])
            ->reject(fn ($contacto) => $contacto['nombre'] === '' && $contacto['celular'] === '' && ! $contacto['telefono_fijo'])
            ->values()
            ->all();

        // Cargar datos sin guardar
        $persona->fill(collect($data)->only($persona->getFillable())->all());
        $inscripcion->fill(collect($data)->only(['forma_pago', 'tiene_padrino', 'padrino_nombre', 'padrino_telefono', 'padrino_presente', 'padrino_parroquia'])->all());

        if (! $inscripcion->tiene_padrino) {
            $inscripcion->fill(['padrino_nombre' => null, 'padrino_telefono' => null, 'padrino_presente' => null, 'padrino_parroquia' => null]);
        }

        //  Los contactos son filas aparte: se comparan como texto
        // para saber si cambiaron, porque isDirty no los ve.
        $contactosAntes   = $inscripcion->contactosEmergencia()->get(['nombre', 'celular', 'telefono_fijo', 'correo', 'parentesco'])->toJson();
        $cambianContactos = json_encode($data['contactos_emergencia']) !== $contactosAntes;

        // No hubo cambios
        if (! $persona->isDirty() && ! $inscripcion->isDirty() && ! $cambianContactos) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        DB::transaction(function () use ($persona, $inscripcion, $data, $cambianContactos) {
            $persona->save();
            $inscripcion->save();

            if ($cambianContactos) {
                $inscripcion->contactosEmergencia()->delete();
                $inscripcion->contactosEmergencia()->createMany($data['contactos_emergencia']);
            }
        });

        //  Desde Llegada se vuelve a Llegada
        $destino = $request->input('volver') === 'llegada' ? route('llegada.index') : route('inscritos.show', $inscripcion);

        return redirect($destino)->with('mensajeActualizado', 'Datos actualizados: ' . $persona->nombre_completo . '.');
    }



    //  Confirmar datos, marcar que no asistió o abandonó, o devolverlo
    // a la lista. Nunca se borra: así tesorería conserva sus pagos.
    public function cambiarEstado(Request $request, Inscripcion $inscripcion)
    {
        $request->validate([
            'estado' => ['required', 'in:' . implode(',', [Inscripcion::INSCRITO, Inscripcion::CONFIRMADO, Inscripcion::NO_ASISTIO, Inscripcion::ABANDONO])],
        ], [
            'estado.in' => 'Ese estado no se puede elegir desde aquí.',
        ]);

        if ($inscripcion->estado === $request->estado) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $cambios = ['estado' => $request->estado];

        //  Quien abandona el retiro CONSERVA su mesa y silla: las sillas no se
        // cambian nunca, así que la suya queda vacía (en las listas no sale y
        // nadie más la puede ocupar). Quien no asistió nunca se sentó: se le
        // quita el puesto si alguien se lo había escrito.
        if ($request->estado === Inscripcion::NO_ASISTIO) {
            $cambios['mesa_id'] = null;
            $cambios['silla']   = null;
        }

        // Al devolverla a la lista, se le puede volver a marcar la llegada
        if ($request->estado === Inscripcion::INSCRITO) {
            $cambios['llegada_en'] = null;
        }

        $inscripcion->update($cambios);

        return back()->with('mensajeActualizado', 'Estado cambiado a: ' . $inscripcion->nombre_estado . '.');
    }



    //  Manda (o vuelve a mandar) el correo de confirmación de cupo. Si el
    // servidor de correo falla, se avisa sin perder nada.
    public function enviarConfirmacion(Inscripcion $inscripcion)
    {
        $correo = $inscripcion->persona->correo;

        if (! $correo) {
            return back()->with('error', 'Esta persona no tiene correo.');
        }

        try {
            Mail::to($correo)->send(new ConfirmacionInscripcion($inscripcion));
        } catch (\Throwable $error) {
            Log::error('No se pudo enviar la confirmación', ['inscripcion' => $inscripcion->id, 'error' => $error->getMessage()]);

            return back()->with('error', 'No se pudo enviar el correo. Revisa la configuración del correo del servidor.');
        }

        $inscripcion->update(['confirmacion_enviada' => now()]);

        return back()->with('mensajeActualizado', 'Se envió el correo de confirmación a ' . $correo . '.');
    }



    //  Los comprobantes están en una carpeta privada; solo se ven
    // pasando por aquí, con la sesión y el rol revisados.
    public function comprobante(Inscripcion $inscripcion)
    {
        if (! $inscripcion->comprobante || ! Storage::exists($inscripcion->comprobante)) {
            return back()->with('error', 'Esta inscripción no tiene comprobante.');
        }

        return Storage::response($inscripcion->comprobante);
    }



    private function conteos(Retiro $retiro): array
    {
        $filas = Inscripcion::where('retiro_id', $retiro->id)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->selectRaw("SUM(inscripcions.estado NOT IN ('abandono', 'no_asistio') AND personas.sexo = 'Masculino') AS hombres")
            ->selectRaw("SUM(inscripcions.estado NOT IN ('abandono', 'no_asistio') AND personas.sexo = 'Femenino') AS mujeres")
            ->selectRaw("SUM(inscripcions.estado IN ('abandono', 'no_asistio')) AS fuera")
            ->first();

        return [
            'hombres' => (int) $filas->hombres,
            'mujeres' => (int) $filas->mujeres,
            'fuera'   => (int) $filas->fuera,
        ];
    }
}
