<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarEquiposSantisimoRequest;
use App\Http\Requests\RepartirSantisimoRequest;
use App\Http\Requests\StoreReglaSantisimoRequest;
use App\Models\Equipo;
use App\Models\Retiro;
use App\Models\SantisimoBase;
use App\Models\SantisimoRegla;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SantisimoRepartoController extends Controller
{

    //  Tamaño de los equipos, sus horas fijas o prohibidas y el botón para
    // repartir el horario.
    public function index()
    {
        return view('santisimo.reparto', [
            'retiro'  => Retiro::actual(),
            'equipos' => Equipo::orderBy('nombre')->get(),
            'reglas'  => SantisimoRegla::with('equipo')->orderBy('tipo')->orderBy('dia_relativo')->orderBy('hora_desde')->get(),
        ]);
    }



    public function store(StoreReglaSantisimoRequest $request)
    {
        SantisimoRegla::create($request->validated());

        return back()->with('mensajeCreado', 'Regla agregada.');
    }



    public function destroy(SantisimoRegla $regla)
    {
        $regla->delete();

        return back()->with('mensajeActualizado', 'Regla quitada.');
    }



    public function guardarEquipos(GuardarEquiposSantisimoRequest $request)
    {
        $datos     = $request->validated('equipos');
        $cambiados = 0;

        DB::transaction(function () use ($datos, &$cambiados) {
            foreach (Equipo::whereIn('id', array_keys($datos))->get() as $equipo) {
                $equipo->fill([
                    'cantidad_personas'   => (int) $datos[$equipo->id]['personas'],
                    'participa_santisimo' => (bool) ($datos[$equipo->id]['participa'] ?? false),
                ]);

                if ($equipo->isDirty()) {
                    $equipo->save();
                    $cambiados++;
                }
            }
        });

        // No hubo cambios
        if (! $cambiados) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        return back()->with('mensajeActualizado', $cambiados === 1 ? 'Se guardó 1 equipo.' : 'Se guardaron ' . $cambiados . ' equipos.');
    }



    //  Reparte la base o la copia del retiro. Las franjas de TODOS no se
    // tocan; las demás se vuelven a asignar desde cero.
    public function repartir(RepartirSantisimoRequest $request)
    {
        $destino = $request->validated('destino');
        $turno   = (int) $request->validated('turno');

        if ($destino === 'retiro') {
            $retiro = Retiro::actual();

            if (! $retiro) {
                return back()->with('error', 'No hay un retiro próximo.');
            }

            $franjas = $retiro->santisimoFranjas()->orderBy('inicio')->get();

            //  La franja de las 0:30 del sábado es del sábado: aquí cuenta el
            // día del calendario, igual que en las reglas.
            $posicion = fn ($franja) => [
                'dia'  => (int) $retiro->fecha_inicio->copy()->startOfDay()->diffInDays($franja->inicio->copy()->startOfDay()),
                'hora' => $franja->inicio->format('H:i:s'),
            ];
        } else {
            $franjas  = SantisimoBase::orderBy('dia_relativo')->orderBy('hora_inicio')->get();
            $posicion = fn ($franja) => ['dia' => $franja->dia_relativo, 'hora' => $franja->hora_inicio];
        }

        if ($franjas->isEmpty()) {
            return back()->with('error', $destino === 'retiro' ? 'Este retiro todavía no tiene horario: primero cópialo de la base.' : 'El horario base está vacío.');
        }

        $asignacion = $this->calcularReparto($franjas, $posicion, $turno);

        DB::transaction(function () use ($franjas, $asignacion) {
            foreach ($franjas as $franja) {
                if ($franja->todos) {
                    continue;
                }

                $franja->equipo_id = $asignacion[$franja->id] ?? null;

                if ($franja->isDirty()) {
                    $franja->save();
                }
            }
        });

        $vacias = $franjas->filter(fn ($franja) => ! $franja->todos && empty($asignacion[$franja->id]))->count();
        $ruta   = $destino === 'retiro' ? 'santisimo.editar' : 'santisimo.base';

        if ($vacias) {
            return redirect()->route($ruta)->with('mensajeAlerta', 'Se repartió el horario, pero ' . $vacias . ($vacias === 1 ? ' franja quedó' : ' franjas quedaron') . ' sin nadie: ningún equipo podía ir a esa hora. Revisa las reglas o asígnalas a mano.');
        }

        return redirect()->route($ruta)->with('mensajeActualizado', 'Listo: se repartió el horario. Revisa el resumen y corrige a mano lo que haga falta.');
    }



    //  Devuelve [id de franja => id de equipo].
    //  1. Las horas fijas van primero.
    //  2. Cada equipo que participa tiene una meta proporcional a su número
    //     de personas (las horas fijas cuentan para la meta).
    //  3. Se recorre el horario en orden: el equipo de la franja anterior
    //     sigue si no ha completado su turno; si no, entra el que va más
    //     atrasado frente a su meta. Nunca entra un equipo en una hora
    //     prohibida, y el que acaba de terminar turno descansa una franja
    //     si hay otro que pueda ir.
    private function calcularReparto(Collection $franjas, callable $posicion, int $turno): array
    {
        $reglas     = SantisimoRegla::all();
        $fijas      = $reglas->where('tipo', SantisimoRegla::FIJA);
        $prohibidas = $reglas->where('tipo', SantisimoRegla::PROHIBIDA);

        $asignacion = [];
        $libres     = [];

        foreach ($franjas->values() as $orden => $franja) {
            if ($franja->todos) {
                continue;
            }

            $lugar = $posicion($franja);
            $fija  = $fijas->first(fn ($regla) => $regla->incluye($lugar['dia'], $lugar['hora']));

            if ($fija) {
                $asignacion[$franja->id] = $fija->equipo_id;
            } else {
                $libres[] = ['id' => $franja->id, 'orden' => $orden] + $lugar;
            }
        }

        $equipos = Equipo::where('participa_santisimo', true)->get();

        if ($equipos->isEmpty() || ! $libres) {
            return $asignacion;
        }

        //  Un equipo sin tamaño cuenta como de una persona, para que no
        // quede por fuera del todo.
        $pesos     = $equipos->mapWithKeys(fn ($equipo) => [$equipo->id => max(1, $equipo->cantidad_personas)]);
        $porFijas  = collect($asignacion)->countBy();
        $total     = count($libres) + $porFijas->only($pesos->keys()->all())->sum();
        $sumaPesos = $pesos->sum();

        // Lo que le falta a cada equipo después de sus horas fijas
        $metas = $pesos->map(fn ($peso, $id) => max(0, $total * $peso / $sumaPesos - ($porFijas[$id] ?? 0)));

        $llevan   = $pesos->map(fn () => 0)->all();
        $anterior = null;
        $seguidas = 0;
        $orden    = null;

        foreach ($libres as $indice => $libre) {
            //  Si en medio hubo una franja de TODOS o una fija, el turno
            // anterior ya terminó.
            if ($orden !== null && $libre['orden'] !== $orden + 1) {
                $anterior = null;
            }

            $orden = $libre['orden'];

            $posibles = $equipos->pluck('id')->reject(fn ($id) => $prohibidas->contains(
                fn ($regla) => $regla->equipo_id === $id && $regla->incluye($libre['dia'], $libre['hora'])
            ));

            $elegido = null;

            if ($posibles->isNotEmpty()) {
                // Sigue el mismo equipo hasta completar su turno
                if ($anterior && $seguidas < $turno && $posibles->contains($anterior) && $llevan[$anterior] < ceil($metas[$anterior])) {
                    $elegido = $anterior;
                } else {
                    $candidatos = $posibles->count() > 1 ? $posibles->reject(fn ($id) => $id === $anterior) : $posibles;
                    $avance     = ($indice + 1) / count($libres);

                    //  El más atrasado: lo que debería llevar a esta altura
                    // menos lo que lleva.
                    $elegido = $candidatos->sortByDesc(fn ($id) => [
                        round($metas[$id] * $avance - $llevan[$id], 6),
                        $pesos[$id],
                    ])->first();
                }
            }

            if ($elegido) {
                $asignacion[$libre['id']] = $elegido;
                $llevan[$elegido]++;
            }

            $seguidas = $elegido && $elegido === $anterior ? $seguidas + 1 : 1;
            $anterior = $elegido;
        }

        return $asignacion;
    }
}
