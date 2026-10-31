<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgregarFranjaBaseRequest;
use App\Http\Requests\GuardarFranjasRequest;
use App\Models\Equipo;
use App\Models\Retiro;
use App\Models\Rol;
use App\Models\SantisimoBase;
use App\Models\SantisimoFranja;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SantisimoController extends Controller
{

    //  El horario del retiro actual, para todas las cuentas. Pensado para
    // el celular: primero los turnos del propio equipo.
    public function index()
    {
        $retiro  = Retiro::actual();
        $franjas = $retiro ? $retiro->santisimoFranjas()->with('equipo')->orderBy('inicio')->get() : collect();
        $usuario = auth()->user();

        $miEquipo  = $usuario->equipo;
        $misTurnos = $miEquipo ? $franjas->where('equipo_id', $miEquipo->id)->values() : collect();
        $proximo   = $misTurnos->first(fn ($franja) => $franja->fin->gt(now()));
        $ahora     = $franjas->first(fn ($franja) => now()->between($franja->inicio, $franja->fin));

        $puedeEditar = $usuario->tieneRol(Rol::ADMINISTRACION, Rol::SECRETARIA);

        return view('santisimo.index', compact('retiro', 'franjas', 'miEquipo', 'misTurnos', 'proximo', 'ahora', 'puedeEditar'));
    }



    // Editor de la copia del retiro actual
    public function editar()
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return redirect()->route('santisimo.index')->with('error', 'No hay un retiro próximo.');
        }

        $franjas = $retiro->santisimoFranjas()->with('equipo')->orderBy('inicio')->get();

        $filas = $franjas->map(fn ($franja) => [
            'modelo' => $franja,
            'dia'    => $this->nombreDia($franja->inicio->dayOfWeekIso),
            'hora'   => $franja->inicio,
        ]);

        return view('santisimo.editor', [
            'modo'    => 'retiro',
            'retiro'  => $retiro,
            'filas'   => $filas,
            'equipos' => Equipo::orderBy('nombre')->get(),
            'resumen' => $this->resumen($franjas),
        ]);
    }



    // Editor del horario base, el que se copia a cada retiro
    public function base()
    {
        $franjas = SantisimoBase::with('equipo')->orderBy('dia_relativo')->orderBy('hora_inicio')->get();

        // Un viernes cualquiera, solo para mostrar las horas
        $viernes = now()->startOfWeek()->addDays(4)->startOfDay();

        $filas = $franjas->map(fn ($franja) => [
            'modelo' => $franja,
            'dia'    => $this->nombreDia(5 + $franja->dia_relativo),
            'hora'   => $viernes->copy()->addDays($franja->dia_relativo)->setTimeFromTimeString($franja->hora_inicio),
        ]);

        return view('santisimo.editor', [
            'modo'    => 'base',
            'retiro'  => Retiro::actual(),
            'filas'   => $filas,
            'equipos' => Equipo::orderBy('nombre')->get(),
            'resumen' => $this->resumen($franjas),
        ]);
    }



    public function guardar(GuardarFranjasRequest $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return back()->with('error', 'No hay un retiro próximo.');
        }

        $franjas = $retiro->santisimoFranjas()->whereIn('id', array_keys($request->validated('franjas')))->get();

        return $this->aplicarCambios($franjas, $request->validated('franjas'));
    }



    public function guardarBase(GuardarFranjasRequest $request)
    {
        $franjas = SantisimoBase::whereIn('id', array_keys($request->validated('franjas')))->get();

        return $this->aplicarCambios($franjas, $request->validated('franjas'));
    }



    //  Copia la base al retiro actual con sus fechas reales. Si el retiro
    // ya tiene su copia, solo se reemplaza cuando se pide expresamente,
    // porque se pierden los cambios hechos para ese retiro.
    public function copiarBase(Request $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return back()->with('error', 'No hay un retiro próximo.');
        }

        $base = SantisimoBase::orderBy('dia_relativo')->orderBy('hora_inicio')->get();

        if ($base->isEmpty()) {
            return back()->with('error', 'El horario base está vacío.');
        }

        $yaTiene = $retiro->santisimoFranjas()->exists();

        if ($yaTiene && ! $request->boolean('reemplazar')) {
            return back()->with('error', 'Este retiro ya tiene su horario.');
        }

        DB::transaction(function () use ($retiro, $base) {
            $retiro->santisimoFranjas()->delete();

            foreach ($base as $franja) {
                $retiro->santisimoFranjas()->create([
                    'inicio'    => $retiro->fecha_inicio->copy()->addDays($franja->dia_relativo)->setTimeFromTimeString($franja->hora_inicio),
                    'todos'     => $franja->todos,
                    'actividad' => $franja->actividad,
                    'equipo_id' => $franja->equipo_id,
                ]);
            }
        });

        $mensaje = $yaTiene ? 'Se volvió a copiar el horario base.' : 'Listo: el retiro ya tiene su horario, copiado de la base.';

        return redirect()->route('santisimo.editar')->with('mensajeActualizado', $mensaje);
    }



    public function agregarBase(AgregarFranjaBaseRequest $request)
    {
        SantisimoBase::create($request->validated());

        return back()->with('mensajeCreado', 'Franja agregada al horario base.');
    }



    public function quitarBase(SantisimoBase $franja)
    {
        $franja->delete();

        return back()->with('mensajeActualizado', 'Franja quitada del horario base.');
    }



    public function imprimir()
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return redirect()->route('santisimo.index')->with('error', 'No hay un retiro próximo.');
        }

        $franjas = $retiro->santisimoFranjas()->with('equipo')->orderBy('inicio')->get();

        if ($franjas->isEmpty()) {
            return redirect()->route('santisimo.index')->with('error', 'Este retiro todavía no tiene horario del Santísimo.');
        }

        $dias   = $franjas->groupBy(fn ($franja) => $franja->inicio->toDateString());
        $titulo = 'Rotación del Santísimo';

        return view('santisimo.imprimir', compact('retiro', 'dias', 'titulo'));
    }



    //  Guarda solo lo que cambió. Una franja de "todos" no lleva equipo,
    // y una de equipo con actividad vacía se entiende como adoración.
    private function aplicarCambios(Collection $franjas, array $datos)
    {
        $cambiadas = 0;

        DB::transaction(function () use ($franjas, $datos, &$cambiadas) {
            foreach ($franjas as $franja) {
                $fila  = $datos[$franja->id];
                $cubre = $fila['cubre'] ?? null;

                $franja->fill([
                    'todos'     => $cubre === 'todos',
                    'equipo_id' => $cubre && $cubre !== 'todos' ? (int) $cubre : null,
                    'actividad' => trim((string) ($fila['actividad'] ?? '')) ?: null,
                ]);

                if ($franja->isDirty()) {
                    $franja->save();
                    $cambiadas++;
                }
            }
        });

        // No hubo cambios
        if (! $cambiadas) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        return back()->with('mensajeActualizado', $cambiadas === 1 ? 'Se guardó 1 franja.' : 'Se guardaron ' . $cambiadas . ' franjas.');
    }



    //  Cuántas franjas tiene cada equipo, cuántas son de todos y cuántas
    // quedaron sin nadie (el Santísimo nunca debe quedar solo).
    private function resumen(Collection $franjas): array
    {
        return [
            'total'     => $franjas->count(),
            'vacias'    => $franjas->filter(fn ($franja) => $franja->estaVacia())->count(),
            'todos'     => $franjas->where('todos', true)->count(),
            'porEquipo' => $franjas->whereNotNull('equipo_id')->where('todos', false)
                ->groupBy('equipo_id')
                ->map(fn ($grupo) => ['nombre' => $grupo->first()->equipo->nombre, 'franjas' => $grupo->count()])
                ->sortByDesc('franjas')
                ->values(),
        ];
    }



    private function nombreDia(int $diaIso): string
    {
        return [1 => 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'][$diaIso];
    }
}
