<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMesaRequest;
use App\Http\Requests\UpdateMesaRequest;
use App\Models\Mesa;
use App\Models\NombreMesa;
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



    //  Nombres de mesas: el santo de cada número, por grupo. No cambia de un
    // retiro a otro; una mesa nueva sin nombre toma el de su número.
    public function nombres()
    {
        $guardados = NombreMesa::orderBy('numero')->get()->groupBy('sexo');
        $retiro    = Retiro::actual();
        $filas     = [];

        foreach (['Masculino', 'Femenino'] as $sexo) {
            $delGrupo = ($guardados[$sexo] ?? collect())->keyBy('numero');
            $mayor    = max(10, (int) $delGrupo->keys()->max(), (int) $retiro?->mesas()->where('sexo', $sexo)->max('numero'));

            //  Dos renglones más para agregar mesas nuevas
            $filas[$sexo] = collect(range(1, min(99, $mayor + 2)))->mapWithKeys(fn ($numero) => [$numero => $delGrupo[$numero]->nombre ?? '']);
        }

        return view('mesas.nombres', compact('filas', 'retiro'));
    }



    public function guardarNombres(Request $request)
    {
        $data = $request->validate([
            'nombres'             => ['array'],
            'nombres.*'           => ['array'],
            'nombres.*.*'         => ['nullable', 'string', 'max:60'],
            'ponerEnEsteRetiro'   => ['nullable', 'boolean'],
        ], [
            'nombres.*.*.max' => 'Un nombre de mesa es demasiado largo (máximo 60 letras).',
        ]);

        foreach (['Masculino', 'Femenino'] as $sexo) {
            foreach ($data['nombres'][$sexo] ?? [] as $numero => $nombre) {
                $numero = (int) $numero;
                $nombre = trim((string) $nombre);

                if ($numero < 1 || $numero > 99) {
                    continue;
                }

                //  Vacío: ese número queda sin nombre guardado
                if ($nombre === '') {
                    NombreMesa::where('sexo', $sexo)->where('numero', $numero)->delete();
                } else {
                    NombreMesa::updateOrCreate(['sexo' => $sexo, 'numero' => $numero], ['nombre' => $nombre]);
                }
            }
        }

        //  Las mesas del retiro actual que todavía no tienen nombre lo toman ya
        $puestas = 0;
        $retiro  = Retiro::actual();

        if ($retiro && $request->boolean('ponerEnEsteRetiro')) {
            foreach ($retiro->mesas()->where(fn ($consulta) => $consulta->whereNull('nombre')->orWhere('nombre', ''))->get() as $mesa) {
                $nombre = NombreMesa::para($mesa->sexo, $mesa->numero);

                if ($nombre) {
                    $mesa->update(['nombre' => $nombre]);
                    $puestas++;
                }
            }
        }

        return redirect()->route('mesas.nombres')->with('mensajeActualizado', 'Nombres de mesas guardados.' . ($puestas ? ' ' . $puestas . ' ' . ($puestas === 1 ? 'mesa de este retiro tomó su nombre.' : 'mesas de este retiro tomaron su nombre.') : ''));
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
