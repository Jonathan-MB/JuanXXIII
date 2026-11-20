<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Inscripcion;
use App\Models\Pago;
use App\Models\Retiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PagoController extends Controller
{

    //  La lista de tesorería: TODOS los inscritos del retiro, también
    // los que no asistieron o abandonaron, porque sus pagos siguen ahí.
    public function index(Request $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return view('pagos.index', ['retiro' => null]);
        }

        $busqueda = trim((string) $request->busqueda);
        $pago     = in_array($request->pago, ['sin_pago', 'abono', 'completo'], true) ? $request->pago : null;

        $consulta = Inscripcion::with(['persona', 'retiro', 'pagos'])
            ->select('inscripcions.*')
            ->withSum('pagos', 'valor')
            ->where('retiro_id', $retiro->id)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->orderBy('personas.nombres')
            ->orderBy('personas.apellidos');

        if ($busqueda !== '') {
            $cedula = preg_replace('/[\s.\-]/', '', $busqueda);
            $consulta->where(function ($donde) use ($busqueda, $cedula) {
                $donde->whereRaw("CONCAT(personas.nombres, ' ', personas.apellidos) LIKE ?", ['%' . $busqueda . '%'])
                    ->orWhere('personas.cedula', 'like', '%' . $cedula . '%')
                    ->orWhere('inscripcions.padrino_nombre', 'like', '%' . $busqueda . '%');
            });
        }

        $inscritos = $consulta->get();

        //  El resumen de arriba es de todo el retiro: se cuenta antes de
        // filtrar por pago, para que no cambie al buscar.
        $resumen = [
            'recaudado' => (float) Pago::whereIn('inscripcion_id', Inscripcion::where('retiro_id', $retiro->id)->select('id'))->sum('valor'),
            'completo'  => $inscritos->where('estado_pago', 'completo')->count(),
            'abono'     => $inscritos->where('estado_pago', 'abono')->count(),
            'sin_pago'  => $inscritos->where('estado_pago', 'sin_pago')->count(),
        ];

        if ($pago) {
            $inscritos = $inscritos->filter(fn ($inscrito) => $inscrito->estado_pago === $pago)->values();
        }

        return view('pagos.index', compact('retiro', 'inscritos', 'busqueda', 'pago', 'resumen'));
    }



    //  Hoja de pagos para tesorería, para llenar a mano: por grupo y en
    // orden alfabético, con lo pagado, lo que falta y dos casillas
    // ("Terminó de pagar" y "No asistió"). Van todos los inscritos.
    public function imprimir()
    {
        $retiro = Retiro::actual();

        abort_unless($retiro, 404);

        $grupos = Inscripcion::with(['persona', 'retiro', 'pagos'])
            ->select('inscripcions.*')
            ->withSum('pagos', 'valor')
            ->where('retiro_id', $retiro->id)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->orderByRaw("personas.sexo = 'Femenino'")
            ->orderBy('personas.nombres')
            ->orderBy('personas.nombres')
            ->orderBy('personas.apellidos')
            ->get()
            ->groupBy(fn ($inscrito) => $inscrito->persona->sexo);

        return view('pagos.imprimir', [
            'retiro'      => $retiro,
            'grupos'      => $grupos,
            'titulo'      => 'Lista de pagos',
            'volver'      => route('pagos.index'),
            'volverTexto' => 'Volver a pagos',
        ]);
    }



    public function store(StorePagoRequest $request, Inscripcion $inscripcion)
    {
        $data = $request->validated();

        $data['comprobante']    = $request->hasFile('comprobante')
            ? $request->file('comprobante')->store('comprobantes/retiro-' . $inscripcion->retiro_id . '/pagos')
            : null;
        $data['inscripcion_id'] = $inscripcion->id;
        $data['usuario_id']     = auth()->id();

        Pago::create($data);

        return redirect()->to(route('inscritos.show', $inscripcion) . '#pagos')->with('mensajeCreado', 'Pago registrado.');
    }



    public function update(StorePagoRequest $request, Pago $pago)
    {
        $data = collect($request->validated())->except('comprobante')->all();

        // Cargar datos sin guardar
        $pago->fill($data);

        if ($request->hasFile('comprobante')) {
            $pago->comprobante = $request->file('comprobante')->store('comprobantes/retiro-' . $pago->inscripcion->retiro_id . '/pagos');
        }

        // No hubo cambios
        if (! $pago->isDirty()) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $pago->save();

        return redirect()->to(route('inscritos.show', $pago->inscripcion_id) . '#pagos')->with('mensajeActualizado', 'Pago actualizado.');
    }



    public function comprobante(Pago $pago)
    {
        if (! $pago->comprobante || ! Storage::exists($pago->comprobante)) {
            return back()->with('error', 'Este pago no tiene comprobante.');
        }

        return Storage::response($pago->comprobante);
    }
}
