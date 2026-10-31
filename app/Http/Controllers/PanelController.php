<?php

namespace App\Http\Controllers;

use App\Models\Inscripcion;
use App\Models\Retiro;
use App\Models\Rol;
use App\Models\TareaCronograma;
use App\Models\TareaCumplida;
use Illuminate\Support\Collection;

class PanelController extends Controller
{

    public function index()
    {
        $retiro = Retiro::actual();

        //  Inscritos por sexo, sin contar a quien no asistió o abandonó
        $inscritos = ['Masculino' => 0, 'Femenino' => 0];

        if ($retiro) {
            $inscritos = array_merge($inscritos, $retiro->inscripciones()
                ->whereNotIn('estado', [Inscripcion::NO_ASISTIO, Inscripcion::ABANDONO])
                ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
                ->selectRaw('personas.sexo, COUNT(*) as total')
                ->groupBy('personas.sexo')
                ->pluck('total', 'sexo')
                ->all());
        }

        $cronograma = $retiro && auth()->user()->tieneRol(Rol::ADMINISTRACION, Rol::SECRETARIA, Rol::DIRECTIVOS)
            ? $this->cronograma($retiro)
            : collect();

        return view('panel', compact('retiro', 'inscritos', 'cronograma'));
    }



    //  Las tareas del retiro agrupadas por día, cada una con su hora
    // límite y su estado: hecha, alerta (falta 1 hora o menos, o ya
    // pasó sin marcarse) o pendiente.
    private function cronograma(Retiro $retiro): Collection
    {
        $hechas = TareaCumplida::with('usuario')
            ->where('retiro_id', $retiro->id)
            ->get()
            ->keyBy('tarea_cronograma_id');

        return TareaCronograma::orderBy('dia_relativo')
            ->orderBy('hora')
            ->get()
            ->map(function ($tarea) use ($retiro, $hechas) {
                $limite = $tarea->limiteEn($retiro);
                $hecha  = $hechas[$tarea->id] ?? null;

                $estado = match (true) {
                    (bool) $hecha                                                  => 'hecha',
                    now()->gte($limite->copy()->subMinutes(TareaCronograma::MINUTOS_ALERTA)) => 'alerta',
                    default                                                        => 'pendiente',
                };

                return (object) ['tarea' => $tarea, 'limite' => $limite, 'hecha' => $hecha, 'estado' => $estado];
            })
            ->groupBy(fn ($fila) => $fila->tarea->dia_relativo);
    }
}
