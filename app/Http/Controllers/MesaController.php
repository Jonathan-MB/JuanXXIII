<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMesaRequest;
use App\Http\Requests\UpdateMesaRequest;
use App\Models\Mesa;
use App\Models\Retiro;
use Illuminate\Http\Request;

class MesaController extends Controller
{

    public function index(Request $request)
    {
        $retiro = Retiro::actual();
        $sexo   = $request->sexo === 'mujeres' ? 'Femenino' : 'Masculino';

        $mesas = $retiro
            ? $retiro->mesas()->where('sexo', $sexo)->withCount(['inscripciones', 'inscripciones as sentados_count' => fn ($consulta) => $consulta->where('estado', '!=', \App\Models\Inscripcion::ABANDONO)])->orderBy('numero')->get()
            : collect();

        return view('mesas.index', compact('retiro', 'sexo', 'mesas'));
    }



    // Agrega una mesa cuando hace falta (también se crean solas al escribir un puesto)
    public function store(StoreMesaRequest $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return back()->with('error', 'No hay un retiro próximo.');
        }

        $mesa = $retiro->mesas()->create($request->validated());

        return redirect()->route('mesas.index', ['sexo' => $mesa->sexo === 'Femenino' ? 'mujeres' : 'hombres'])
            ->with('mensajeCreado', 'Mesa ' . $mesa->numero . ' agregada.');
    }



    public function update(UpdateMesaRequest $request, Mesa $mesa)
    {
        $data = $request->validated();

        //  No se pueden quitar sillas que ya tienen a alguien sentado
        $sillaMasAlta = (int) $mesa->inscripciones()->max('silla');

        if ($data['cantidad_sillas'] < $sillaMasAlta) {
            return back()->with('error', 'La mesa ' . $mesa->numero . ' tiene a alguien en la silla ' . $sillaMasAlta . '. Muévelo antes de quitar sillas.');
        }

        // Cargar datos sin guardar
        $mesa->fill($data);

        // No hubo cambios
        if (! $mesa->isDirty()) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios en la mesa ' . $mesa->numero . '.');
        }

        $mesa->save();

        return back()->with('mensajeActualizado', 'Mesa ' . $mesa->numero . ' actualizada.');
    }



    //  Solo se quita una mesa vacía. Si alguien ya está sentado ahí,
    // primero hay que moverlo, para no perder su puesto sin darse cuenta.
    public function destroy(Mesa $mesa)
    {
        if ($mesa->inscripciones()->exists()) {
            return back()->with('error', 'La mesa ' . $mesa->numero . ' tiene personas sentadas. Muévelas antes de quitarla.');
        }

        $mesa->delete();

        return back()->with('mensajeActualizado', 'Mesa ' . $mesa->numero . ' quitada.');
    }
}
