<?php

namespace App\Http\Controllers;

use App\Http\Requests\TareaCronogramaRequest;
use App\Models\Retiro;
use App\Models\TareaCronograma;
use App\Models\TareaCumplida;

class CronogramaController extends Controller
{

    // La lista de tareas, la misma para todos los retiros
    public function index()
    {
        $tareas = TareaCronograma::orderBy('dia_relativo')->orderBy('hora')->get();

        return view('cronograma.index', compact('tareas'));
    }



    //  La lista para imprimir y marcar a mano: por día, con la fecha real
    // del retiro actual. Las que ya se marcaron en la app salen hechas.
    public function imprimir()
    {
        $retiro = Retiro::actual();
        $hechas = $retiro
            ? TareaCumplida::where('retiro_id', $retiro->id)->pluck('tarea_cronograma_id')->flip()
            : collect();

        $dias = TareaCronograma::orderBy('dia_relativo')->orderBy('hora')->get()
            ->groupBy('dia_relativo')
            ->map(fn ($tareas, $dia) => [
                // "3 días antes · martes 17 de noviembre"; los días del retiro, solo "Viernes 20 de noviembre"
                'nombre' => ! $retiro ? TareaCronograma::nombreDia((int) $dia)
                    : ((int) $dia >= 0 ? '' : TareaCronograma::nombreDia((int) $dia) . ' · ')
                        . \Illuminate\Support\Str::ucfirst($retiro->fecha_inicio->copy()->addDays((int) $dia)->locale('es')->translatedFormat('l j \d\e F')),
                'tareas' => $tareas,
            ]);

        return view('cronograma.imprimir', [
            'retiro'      => $retiro,
            'dias'        => $dias,
            'hechas'      => $hechas,
            'titulo'      => 'Lista de tareas',
            'volver'      => route('panel') . '#cronograma',
            'volverTexto' => 'Volver al inicio',
        ]);
    }



    public function store(TareaCronogramaRequest $request)
    {
        TareaCronograma::create($request->validated());

        return redirect()->route('cronograma.index')->with('mensajeCreado', 'Tarea agregada.');
    }



    public function update(TareaCronogramaRequest $request, TareaCronograma $tarea)
    {
        // Cargar datos sin guardar
        $tarea->fill($request->validated());

        // La hora llega como "17:30" y se guarda como "17:30:00": no es un cambio
        if (substr((string) $tarea->getOriginal('hora'), 0, 5) === substr((string) $tarea->hora, 0, 5)) {
            $tarea->hora = $tarea->getOriginal('hora');
        }

        // No hubo cambios
        if (! $tarea->isDirty()) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $tarea->save();

        return back()->with('mensajeActualizado', 'Tarea actualizada.');
    }



    //  Quitar una tarea de la lista también borra sus marcas en los
    // retiros: es configuración, no datos de las personas.
    public function destroy(TareaCronograma $tarea)
    {
        $tarea->delete();

        return back()->with('mensajeActualizado', 'Tarea quitada de la lista.');
    }



    // Marca o desmarca una tarea en el retiro actual
    public function marcar(TareaCronograma $tarea)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return back()->with('error', 'No hay un retiro próximo.');
        }

        $cumplida = TareaCumplida::where('tarea_cronograma_id', $tarea->id)->where('retiro_id', $retiro->id)->first();

        if ($cumplida) {
            $cumplida->delete();

            return redirect()->to(route('panel') . '#cronograma')->with('mensajeAlerta', 'Se desmarcó: ' . $tarea->descripcion);
        }

        TareaCumplida::create([
            'tarea_cronograma_id' => $tarea->id,
            'retiro_id'           => $retiro->id,
            'usuario_id'          => auth()->id(),
            'hecha_en'            => now(),
        ]);

        return redirect()->to(route('panel') . '#cronograma')->with('mensajeActualizado', 'Hecho: ' . $tarea->descripcion);
    }
}
