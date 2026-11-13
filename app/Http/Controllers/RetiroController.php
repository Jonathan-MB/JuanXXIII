<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRetiroRequest;
use App\Http\Requests\UpdateRetiroRequest;
use App\Models\Retiro;
use Illuminate\Support\Facades\DB;

class RetiroController extends Controller
{

    public function index()
    {
        Retiro::ponerAlDia();

        $retiros = Retiro::orderByDesc('fecha_inicio')->get();

        return view('retiros.index', compact('retiros'));
    }



    public function create()
    {
        // Se propone el número siguiente al último retiro creado
        $ultimo = Retiro::orderByDesc('fecha_inicio')->first();

        $retiro = new Retiro([
            'numero_hombres'     => $ultimo ? $ultimo->numero_hombres + 1 : null,
            'numero_mujeres'     => $ultimo ? $ultimo->numero_mujeres + 1 : null,
            'hora_inicio'        => '17:30',
            'hora_fin'           => '16:00',
            'porcentaje_reserva' => 50,
            'ofrenda'            => $ultimo?->ofrenda,
            'que_incluye'        => $ultimo?->que_incluye,
            'lugar'              => $ultimo?->lugar,
            'direccion'          => $ultimo?->direccion,
            'ciudad'             => $ultimo?->ciudad ?? 'Santiago de Cali',
        ]);

        //  Las cuentas de pago y los contactos del retiro anterior se
        // copian como punto de partida; casi siempre se repiten.
        $cuentasPago       = $ultimo ? $ultimo->cuentasPago()->get(['entidad', 'numero', 'titular'])->toArray() : [];
        $contactosInformes = $ultimo ? $ultimo->contactosInformes()->get(['nombre', 'celular'])->toArray() : [];

        return view('retiros.formulario', compact('retiro', 'cuentasPago', 'contactosInformes'));
    }



    public function store(StoreRetiroRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $retiro = Retiro::create($data);
            $this->unicoProximo($retiro);
            $retiro->cuentasPago()->createMany($data['cuentas_pago'] ?? []);
            $retiro->contactosInformes()->createMany($data['contactos_informes'] ?? []);
        });

        return redirect()->route('retiros.index')->with('mensajeCreado', 'Retiro creado.');
    }



    public function edit(Retiro $retiro)
    {
        $cuentasPago       = $retiro->cuentasPago()->get(['entidad', 'numero', 'titular'])->toArray();
        $contactosInformes = $retiro->contactosInformes()->get(['nombre', 'celular'])->toArray();

        return view('retiros.formulario', compact('retiro', 'cuentasPago', 'contactosInformes'));
    }



    public function update(UpdateRetiroRequest $request, Retiro $retiro)
    {
        $data = $request->validated();

        // PATCH sin data
        if (empty($data)) {
            return back()->with('mensajeAlerta', 'Sin datos');
        }

        // Cargar datos sin guardar
        $retiro->fill($data);

        //  Las cuentas y los contactos son filas aparte: se comparan
        // como texto para saber si cambiaron, porque isDirty no los ve.
        $cuentasAntes     = $retiro->cuentasPago()->get(['entidad', 'numero', 'titular'])->toJson();
        $contactosAntes   = $retiro->contactosInformes()->get(['nombre', 'celular'])->toJson();
        $cambianCuentas   = array_key_exists('cuentas_pago', $data) && json_encode($data['cuentas_pago']) !== $cuentasAntes;
        $cambianContactos = array_key_exists('contactos_informes', $data) && json_encode($data['contactos_informes']) !== $contactosAntes;

        // No hubo cambios
        if (! $retiro->isDirty() && ! $cambianCuentas && ! $cambianContactos) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        DB::transaction(function () use ($retiro, $data, $cambianCuentas, $cambianContactos) {
            $retiro->save();
            $this->unicoProximo($retiro);

            //  Al cerrar las inscripciones de este retiro se abren las del próximo
            if ($retiro->wasChanged('inscripciones_abiertas') && ! $retiro->inscripciones_abiertas) {
                $retiro->abrirElProximo();
            }

            if ($cambianCuentas) {
                $retiro->cuentasPago()->delete();
                $retiro->cuentasPago()->createMany($data['cuentas_pago']);
            }

            if ($cambianContactos) {
                $retiro->contactosInformes()->delete();
                $retiro->contactosInformes()->createMany($data['contactos_informes']);
            }
        });

        return redirect()->route('retiros.index')->with('mensajeActualizado', 'Retiro actualizado.');
    }



    // Solo un retiro a la vez puede ser el próximo
    private function unicoProximo(Retiro $retiro): void
    {
        if ($retiro->es_proximo) {
            Retiro::where('id', '!=', $retiro->id)->where('es_proximo', true)->update(['es_proximo' => false]);
        }
    }
}
