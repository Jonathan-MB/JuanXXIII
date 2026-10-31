<?php

namespace App\Http\Controllers;

use App\Http\Requests\AgregarActividadRequest;
use App\Http\Requests\GuardarActividadesRequest;
use App\Models\ActividadBase;
use App\Models\ActividadRetiro;
use App\Models\Retiro;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DesarrolloController extends Controller
{

    //  El "Desarrollo del retiro": el horario de actividades y charlas que
    // usa Secretaría para el papeleo. Uno por sexo.
    private const SEXOS = [
        'hombres' => 'Masculino',
        'mujeres' => 'Femenino',
    ];

    // Lo que se imprime con el horario del retiro (?formato=)
    private const FORMATOS = [
        'desarrollo' => 'Desarrollo del retiro',
        'charlistas' => 'Lista de charlistas',
        'sala'       => 'Rotación al Santísimo en sala',
    ];



    // Para leer (también en el celular durante el retiro)
    public function index(Request $request)
    {
        $grupo  = $this->grupo($request);
        $retiro = Retiro::actual();

        $actividades = $retiro ? $this->actividadesDelRetiro($retiro, $grupo) : collect();
        $jornadas    = $retiro ? $actividades->groupBy(fn ($actividad) => $actividad->jornadaEn($retiro)) : collect();
        $ahora       = $actividades->first(fn ($actividad) => now()->gte($actividad->inicio) && $actividad->fin && now()->lt($actividad->fin));

        return view('desarrollo.index', compact('grupo', 'retiro', 'actividades', 'jornadas', 'ahora'));
    }



    // Editor de la copia del retiro actual
    public function editar(Request $request)
    {
        $grupo  = $this->grupo($request);
        $retiro = Retiro::actual();

        if (! $retiro) {
            return redirect()->route('desarrollo.index')->with('error', 'No hay un retiro próximo.');
        }

        $filas = $this->actividadesDelRetiro($retiro, $grupo)->map(fn ($actividad) => [
            'id'        => $actividad->id,
            'dia'       => $actividad->jornadaEn($retiro),
            'inicio'    => $actividad->inicio->format('H:i'),
            'fin'       => $actividad->fin?->format('H:i'),
            'nombre'    => $actividad->nombre,
            'es_charla' => $actividad->es_charla,
            'charlista' => $actividad->charlista,
        ]);

        return view('desarrollo.editor', ['modo' => 'retiro', 'grupo' => $grupo, 'retiro' => $retiro, 'filas' => $filas]);
    }



    // Editor de la base: la que se copia a cada retiro
    public function base(Request $request)
    {
        $grupo = $this->grupo($request);

        $filas = ActividadBase::where('sexo', self::SEXOS[$grupo])->get()
            ->sortBy(fn ($actividad) => $actividad->dia_relativo . $actividad->orden)
            ->values()
            ->map(fn ($actividad) => [
                'id'        => $actividad->id,
                'dia'       => $actividad->dia_relativo,
                'inicio'    => substr($actividad->hora_inicio, 0, 5),
                'fin'       => $actividad->hora_fin ? substr($actividad->hora_fin, 0, 5) : null,
                'nombre'    => $actividad->nombre,
                'es_charla' => $actividad->es_charla,
                'charlista' => null,
            ]);

        return view('desarrollo.editor', ['modo' => 'base', 'grupo' => $grupo, 'retiro' => Retiro::actual(), 'filas' => $filas]);
    }



    public function guardar(GuardarActividadesRequest $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return back()->with('error', 'No hay un retiro próximo.');
        }

        $datos       = $request->validated('actividades');
        $actividades = $retiro->actividades()->whereIn('id', array_keys($datos))->get();

        return $this->aplicarCambios($actividades, $datos, function ($actividad, $fila) use ($retiro) {
            $inicio = ActividadRetiro::momento($retiro, $fila['dia_relativo'], $fila['hora_inicio']);

            $actividad->fill([
                'inicio'    => $inicio,
                'fin'       => ActividadRetiro::finDesde($inicio, $fila['hora_fin']),
                'nombre'    => $fila['nombre'],
                'es_charla' => $fila['es_charla'],
                'charlista' => $fila['charlista'],
            ]);
        });
    }



    public function guardarBase(GuardarActividadesRequest $request)
    {
        $datos       = $request->validated('actividades');
        $actividades = ActividadBase::whereIn('id', array_keys($datos))->get();

        return $this->aplicarCambios($actividades, $datos, function ($actividad, $fila) {
            $actividad->fill([
                'dia_relativo' => $fila['dia_relativo'],
                'hora_inicio'  => $fila['hora_inicio'] . ':00',
                'hora_fin'     => $fila['hora_fin'] ? $fila['hora_fin'] . ':00' : null,
                'nombre'       => $fila['nombre'],
                'es_charla'    => $fila['es_charla'],
            ]);
        });
    }



    public function agregar(AgregarActividadRequest $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return back()->with('error', 'No hay un retiro próximo.');
        }

        $data   = $request->validated();
        $inicio = ActividadRetiro::momento($retiro, $data['dia_relativo'], $data['hora_inicio']);

        $retiro->actividades()->create([
            'sexo'      => self::SEXOS[$data['sexo']],
            'inicio'    => $inicio,
            'fin'       => ActividadRetiro::finDesde($inicio, $data['hora_fin']),
            'nombre'    => $data['nombre'],
            'es_charla' => $data['es_charla'],
            'charlista' => $data['charlista'] ?? null,
        ]);

        return back()->with('mensajeCreado', 'Actividad agregada.');
    }



    public function agregarBase(AgregarActividadRequest $request)
    {
        $data = $request->validated();

        ActividadBase::create([
            'sexo'         => self::SEXOS[$data['sexo']],
            'dia_relativo' => $data['dia_relativo'],
            'hora_inicio'  => $data['hora_inicio'] . ':00',
            'hora_fin'     => $data['hora_fin'] ? $data['hora_fin'] . ':00' : null,
            'nombre'       => $data['nombre'],
            'es_charla'    => $data['es_charla'],
        ]);

        return back()->with('mensajeCreado', 'Actividad agregada a la base.');
    }



    //  Copia la base de un sexo al retiro actual. Si ya tiene su copia,
    // solo se reemplaza cuando se pide: se pierden los charlistas y los
    // cambios hechos para ese retiro.
    public function copiarBase(Request $request)
    {
        $grupo  = $this->grupo($request);
        $retiro = Retiro::actual();

        if (! $retiro) {
            return back()->with('error', 'No hay un retiro próximo.');
        }

        $base = ActividadBase::where('sexo', self::SEXOS[$grupo])->get();

        if ($base->isEmpty()) {
            return back()->with('error', 'La base de ' . $grupo . ' está vacía.');
        }

        $yaTiene = $retiro->actividades()->where('sexo', self::SEXOS[$grupo])->exists();

        if ($yaTiene && ! $request->boolean('reemplazar')) {
            return back()->with('error', 'El retiro ya tiene el desarrollo de ' . $grupo . '.');
        }

        DB::transaction(function () use ($retiro, $base, $grupo) {
            $retiro->actividades()->where('sexo', self::SEXOS[$grupo])->delete();

            foreach ($base as $actividad) {
                $inicio = ActividadRetiro::momento($retiro, $actividad->dia_relativo, $actividad->hora_inicio);

                $retiro->actividades()->create([
                    'sexo'      => $actividad->sexo,
                    'inicio'    => $inicio,
                    'fin'       => ActividadRetiro::finDesde($inicio, $actividad->hora_fin),
                    'nombre'    => $actividad->nombre,
                    'es_charla' => $actividad->es_charla,
                ]);
            }
        });

        $mensaje = $yaTiene ? 'Se volvió a copiar la base de ' . $grupo . '.' : 'Listo: el desarrollo de ' . $grupo . ' quedó copiado de la base.';

        return redirect()->route('desarrollo.editar', ['sexo' => $grupo])->with('mensajeActualizado', $mensaje);
    }



    //  Hombres, mujeres o ambos (uno detrás del otro). Con ?formato salen
    // los otros formatos de las carpetas del rectorado que se arman con
    // el mismo horario: la lista de charlistas y la rotación en sala.
    public function imprimir(Request $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return redirect()->route('desarrollo.index')->with('error', 'No hay un retiro próximo.');
        }

        $formato = array_key_exists($request->formato, self::FORMATOS) ? $request->formato : 'desarrollo';
        $grupos  = array_key_exists($request->sexo, self::SEXOS) ? [$request->sexo] : array_keys(self::SEXOS);
        $titulo  = self::FORMATOS[$formato];

        $hojas = collect($grupos)
            ->mapWithKeys(fn ($grupo) => [$grupo => $this->actividadesDelRetiro($retiro, $grupo)])
            ->filter(fn ($actividades) => $actividades->isNotEmpty())
            ->map(fn ($actividades) => $actividades->groupBy(fn ($actividad) => $actividad->jornadaEn($retiro)));

        if ($hojas->isEmpty()) {
            return redirect()->route('desarrollo.index')->with('error', 'El retiro todavía no tiene el desarrollo preparado.');
        }

        $rectorados = $retiro->rectorados()->orderBy('orden')->get()->groupBy('sexo');

        if ($formato === 'charlistas') {
            $hojas = $hojas->map(fn ($jornadas) => $jornadas
                ->map(fn ($delDia) => $delDia->where('es_charla', true)->values())
                ->filter(fn ($charlas) => $charlas->isNotEmpty()));
        }

        if ($formato === 'sala') {
            $hojas = $hojas->map(fn ($jornadas) => $jornadas
                ->map(fn ($delDia) => $this->franjasEnSala($delDia))
                ->filter(fn ($franjas) => $franjas->isNotEmpty()));
        }

        $vista = match ($formato) {
            'charlistas' => 'desarrollo.charlistas',
            'sala'       => 'desarrollo.rotacionSala',
            default      => 'desarrollo.imprimir',
        };

        return view($vista, compact('retiro', 'hojas', 'titulo', 'rectorados'));
    }



    //  Guarda solo lo que cambió y quita las filas marcadas con "quitar".
    // $llenar pasa los datos validados de la fila al modelo.
    private function aplicarCambios(Collection $actividades, array $datos, callable $llenar)
    {
        $cambiadas = 0;
        $quitadas  = 0;

        DB::transaction(function () use ($actividades, $datos, $llenar, &$cambiadas, &$quitadas) {
            foreach ($actividades as $actividad) {
                $fila = $datos[$actividad->id];

                if ($fila['quitar']) {
                    $actividad->delete();
                    $quitadas++;
                    continue;
                }

                $llenar($actividad, $fila);

                if ($actividad->isDirty()) {
                    $actividad->save();
                    $cambiadas++;
                }
            }
        });

        // No hubo cambios
        if (! $cambiadas && ! $quitadas) {
            return back()->with('mensajeAlerta', 'No se detectaron cambios');
        }

        $partes = array_filter([
            $cambiadas ? ($cambiadas === 1 ? '1 actividad guardada' : $cambiadas . ' actividades guardadas') : null,
            $quitadas ? ($quitadas === 1 ? '1 quitada' : $quitadas . ' quitadas') : null,
        ]);

        return back()->with('mensajeActualizado', ucfirst(implode(' y ', $partes)) . '.');
    }



    //  Franjas de una hora mientras hay charlas, para que el rector anote
    // qué catequista acompaña al Santísimo en la sala. La primera empieza
    // con la primera charla y las demás en punto; las horas sin charla
    // (comidas, descanso) no salen.
    private function franjasEnSala(Collection $delDia): Collection
    {
        $charlas = $delDia->where('es_charla', true)->filter(fn ($actividad) => $actividad->fin)->values();

        if ($charlas->isEmpty()) {
            return collect();
        }

        $franjas = collect();
        $inicio  = $charlas->first()->inicio->copy();
        $final   = $charlas->max('fin');

        while ($inicio->lt($final)) {
            $siguiente = $inicio->copy()->startOfHour()->addHour();
            $fin       = $siguiente->lt($final) ? $siguiente : $final->copy();

            $enCurso = $charlas->filter(fn ($charla) => $charla->inicio->lt($fin) && $charla->fin->gt($inicio));

            if ($enCurso->isNotEmpty()) {
                $franjas->push([
                    'inicio'  => $inicio->copy(),
                    'fin'     => $fin,
                    'charlas' => $enCurso->pluck('nombre')->implode(' / '),
                ]);
            }

            $inicio = $siguiente;
        }

        //  Un pedazo de menos de media hora al principio o al final no da
        // para un turno: se une a la franja de al lado.
        if ($franjas->count() > 1 && $franjas->first()['inicio']->diffInMinutes($franjas->first()['fin']) < 30) {
            $primera = $franjas->shift();
            $franjas->transform(fn ($franja, $indice) => $indice === 0 ? $this->unirFranjas($primera, $franja) : $franja);
        }

        if ($franjas->count() > 1 && $franjas->last()['inicio']->diffInMinutes($franjas->last()['fin']) < 30) {
            $ultima = $franjas->pop();
            $franjas->push($this->unirFranjas($franjas->pop(), $ultima));
        }

        return $franjas->values();
    }



    private function unirFranjas(array $antes, array $despues): array
    {
        $charlas = collect(explode(' / ', $antes['charlas']))->merge(explode(' / ', $despues['charlas']))->unique();

        return [
            'inicio'  => $antes['inicio'],
            'fin'     => $despues['fin'],
            'charlas' => $charlas->implode(' / '),
        ];
    }



    private function actividadesDelRetiro(Retiro $retiro, string $grupo): Collection
    {
        return $retiro->actividades()->where('sexo', self::SEXOS[$grupo])->orderBy('inicio')->get();
    }



    private function grupo(Request $request): string
    {
        return array_key_exists($request->sexo, self::SEXOS) ? $request->sexo : 'hombres';
    }
}
