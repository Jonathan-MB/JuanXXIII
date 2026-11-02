<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarFirmantesRequest;
use App\Http\Requests\GuardarPerseveranciasRequest;
use App\Http\Requests\GuardarRectoradoRequest;
use App\Models\ActividadBase;
use App\Models\Ajuste;
use App\Models\Firmante;
use App\Models\Inscripcion;
use App\Models\Mesa;
use App\Models\Perseverancia;
use App\Models\Persona;
use App\Models\Rectorado;
use App\Models\Retiro;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImpresionController extends Controller
{

    //  Los formatos de las carpetas del rectorado que se llenan a mano
    // durante el retiro. La vista de cada uno está en impresiones/informes.
    public const INFORMES = [
        'dirigente'     => 'Informe del dirigente responsable',
        'rector'        => 'Informe del rector(a)',
        'vicerrector'   => 'Informe del vicerrector(a)',
        'equipos'       => 'Evaluación de equipos',
        'problematicas' => 'Problemáticas',
        'ofrenda'       => 'Acta de entrega de la ofrenda',
        'charlas'       => 'Evaluación de charlas',
    ];

    //  Lo que lleva la carpeta de cada cargo, en orden. «charlistas» y
    // «sala» salen del desarrollo; los demás son de aquí.
    public const CARPETAS = [
        'rector' => [
            'nombre'     => 'Carpeta del rector(a)',
            'documentos' => ['charlistas', 'sala', 'catequistas', 'rector', 'problematicas', 'ofrenda'],
        ],
        'vicerrector' => [
            'nombre'     => 'Carpeta del vicerrector(a)',
            'documentos' => ['charlistas', 'vicerrector', 'equipos'],
        ],
        'dirigente' => [
            'nombre'     => 'Carpeta del dirigente responsable',
            'documentos' => ['charlistas', 'dirigente', 'charlas'],
        ],
    ];

    //  Los equipos de servicio tal como van en el informe del
    // vicerrector(a) y en la evaluación de equipos (Formatos Rectorados).
    private const EQUIPOS_SERVICIO = [
        'Montaje',
        'Cocina',
        'Secretaría',
        'Librería',
        'Floristería',
        'Cafetería',
        'Coro',
        'Previstos',
        'Premontaje',
    ];

    // El centro de impresiones: qué se puede imprimir y qué falta
    public function index()
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return view('impresiones.index', ['retiro' => null]);
        }

        $todos = $this->inscritos($retiro, null);

        $avisos = [
            'sinPuesto'      => $todos->whereNull('mesa_id')->count(),
            'sinLlegada'     => $todos->whereNull('llegada_en')->count(),
            'cartas'         => $todos->whereIn('estado', [Inscripcion::LLEGO, Inscripcion::TERMINO])->count(),
            'rectoradoH'     => $retiro->rectorados()->where('sexo', 'Masculino')->count(),
            'rectoradoM'     => $retiro->rectorados()->where('sexo', 'Femenino')->count(),
            'firmantes'      => Firmante::count(),
            'perseverancias' => Perseverancia::count(),
            'mesasSinCt'     => $retiro->mesas()->whereNull('catequista_titular')->count(),
            'proximoRetiro'  => $this->proximoRetiro($retiro),
        ];

        return view('impresiones.index', compact('retiro', 'todos', 'avisos'));
    }



    public function datos()
    {
        $retiro = Retiro::actual();

        $rectorados = $retiro
            ? $retiro->rectorados()->orderBy('orden')->get()->groupBy('sexo')
            : collect();

        return view('impresiones.datos', [
            'retiro'         => $retiro,
            'rectorados'     => $rectorados,
            'firmantes'      => Firmante::orderBy('orden')->get(),
            'perseverancias' => Perseverancia::orderBy('orden')->get(),
        ]);
    }



    //  El rectorado de un sexo se reemplaza completo: son pocas filas
    // y así no hay que comparar una por una.
    public function guardarRectorado(GuardarRectoradoRequest $request)
    {
        $retiro = Retiro::actual();

        if (! $retiro) {
            return back()->with('error', 'No hay un retiro próximo.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($retiro, $data) {
            $retiro->rectorados()->where('sexo', $data['sexo'])->delete();

            foreach ($data['cargos'] as $orden => $cargo) {
                $retiro->rectorados()->create($cargo + ['sexo' => $data['sexo'], 'orden' => $orden + 1]);
            }
        });

        $grupo = $data['sexo'] === 'Femenino' ? 'mujeres' : 'hombres';

        return redirect()->to(route('impresiones.datos') . '#rectorado-' . $grupo)->with('mensajeActualizado', 'Rectorado de ' . $grupo . ' guardado.');
    }



    public function guardarFirmantes(GuardarFirmantesRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            Firmante::query()->delete();

            foreach ($data['firmantes'] as $orden => $firmante) {
                Firmante::create($firmante + ['orden' => $orden + 1]);
            }
        });

        return redirect()->to(route('impresiones.datos') . '#firmantes')->with('mensajeActualizado', 'Firmas de la carta guardadas.');
    }



    public function guardarPerseverancias(GuardarPerseveranciasRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            Perseverancia::query()->delete();

            foreach ($data['perseverancias'] as $orden => $perseverancia) {
                Perseverancia::create($perseverancia + ['orden' => $orden + 1]);
            }
        });

        return redirect()->to(route('impresiones.datos') . '#perseverancias')->with('mensajeActualizado', 'Perseverancias guardadas.');
    }



    //  La carta al presbítero, una por página, solo para quienes
    // llegaron o terminaron: quien abandonó o no asistió no recibe carta.
    public function carta(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        $inscritos = $this->inscritos($retiro, $sexo)
            ->whereIn('estado', [Inscripcion::LLEGO, Inscripcion::TERMINO])
            ->values();

        return view('impresiones.carta', [
            'retiro'    => $retiro,
            'inscritos' => $inscritos,
            'proximo'   => $this->proximoRetiro($retiro),
            'firmantes' => Firmante::orderBy('orden')->get(),
            'titulo'    => 'Cartas al presbítero',
        ]);
    }



    public function correcciones(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.correcciones', [
            'retiro' => $retiro,
            'grupos' => $this->porGrupoYMesa($retiro, $sexo),
            'titulo' => 'Correcciones',
        ]);
    }



    public function directorio(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.directorio', [
            'retiro'         => $retiro,
            'grupos'         => $this->porGrupoYMesa($retiro, $sexo),
            'rectorados'     => $retiro->rectorados()->orderBy('orden')->get()->groupBy('sexo'),
            'perseverancias' => Perseverancia::orderBy('orden')->get(),
            'whatsapp'       => Ajuste::valor(Ajuste::WHATSAPP),
            'tieneQr'        => (bool) Ajuste::valor(Ajuste::WHATSAPP_QR),
            'titulo'         => 'Directorio',
        ]);
    }



    public function ubicacion(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.ubicacion', [
            'retiro' => $retiro,
            'grupos' => $this->porGrupoYMesa($retiro, $sexo),
            'titulo' => 'Hojas de ubicación',
        ]);
    }



    //  Lista para la llegada: por grupo y en orden alfabético, con lo que
    // se necesita en la puerta (documento, edad y padrino) y una casilla.
    public function llegada(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        $grupos = Inscripcion::with('persona')
            ->select('inscripcions.*')
            ->where('inscripcions.retiro_id', $retiro->id)
            ->whereNotIn('inscripcions.estado', Inscripcion::ESTADOS_FUERA)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->when($sexo, fn ($consulta) => $consulta->where('personas.sexo', $sexo))
            ->orderByRaw("personas.sexo = 'Femenino'")
            ->orderBy('personas.apellidos')
            ->orderBy('personas.nombres')
            ->get()
            ->groupBy(fn ($inscrito) => $inscrito->persona->sexo);

        return view('impresiones.llegada', [
            'retiro' => $retiro,
            'grupos' => $grupos,
            'titulo' => 'Lista de llegada',
        ]);
    }



    //  Hojas para que cada persona escriba sus datos en su silla. Usa las
    // mesas que ya existen; si no hay ninguna, 10 mesas de 8 sillas.
    public function mesasEnBlanco(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request) ?? 'Masculino';

        $mesas = $retiro->mesas()->where('sexo', $sexo)->orderBy('numero')->get();

        if ($mesas->isEmpty()) {
            $mesas = collect(range(1, 10))->map(fn ($numero) => new Mesa(['numero' => $numero, 'cantidad_sillas' => 8, 'sexo' => $sexo]));
        }

        return view('impresiones.mesasEnBlanco', [
            'retiro' => $retiro,
            'sexo'   => $sexo,
            'mesas'  => $mesas,
            'titulo' => 'Hojas de mesa en blanco',
        ]);
    }



    //  Para Librería: cuántas camisetas de cada talla y, por mesa, la
    // talla de cada persona (así se reparten en los sobres).
    public function camisetas(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        $conteos = $this->inscritos($retiro, $sexo)
            ->groupBy(fn ($inscrito) => $inscrito->persona->sexo)
            ->map(function ($delGrupo) {
                $porTalla = $delGrupo->countBy(fn ($inscrito) => $inscrito->persona->talla_camiseta);

                // Primero las tallas conocidas en orden; si llega otra, al final
                return collect(Persona::TALLAS)->mapWithKeys(fn ($talla) => [$talla => $porTalla[$talla] ?? 0])
                    ->union($porTalla->except(Persona::TALLAS));
            });

        return view('impresiones.camisetas', [
            'retiro'  => $retiro,
            'grupos'  => $this->porGrupoYMesa($retiro, $sexo),
            'conteos' => $conteos,
            'titulo'  => 'Camisetas por talla',
        ]);
    }



    //  Hoja de entrega de previstos (formato de Secretaría): por mesa,
    // con casillas para lo que recibe cada persona.
    public function previstos(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.previstos', [
            'retiro' => $retiro,
            'grupos' => $this->porGrupoYMesa($retiro, $sexo),
            'titulo' => 'Entrega de previstos',
        ]);
    }



    //  Distribución de catequistas en sala (carpetas del rectorado): cada
    // mesa con su CT y su CA. Las mesas van en U, como se ubican en la
    // sala: la primera mitad baja por la izquierda y el resto sube por la
    // derecha. Si aún no hay mesas, salen 10 en blanco para llenar a mano.
    public function catequistas(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        $mesas      = $retiro->mesas()->orderBy('numero')->get()->groupBy('sexo');
        $rectorados = $retiro->rectorados()->orderBy('orden')->get()->groupBy('sexo');

        $grupos = collect(['Masculino', 'Femenino'])
            ->when($sexo, fn ($sexos) => $sexos->filter(fn ($grupo) => $grupo === $sexo))
            ->mapWithKeys(function ($grupo) use ($mesas) {
                $delGrupo = $mesas->get($grupo, collect())->values();

                if ($delGrupo->isEmpty()) {
                    $delGrupo = collect(range(1, 10))->map(fn ($numero) => new Mesa(['numero' => $numero]));
                }

                $mitad = (int) ceil($delGrupo->count() / 2);

                return [$grupo => [
                    'izquierda' => $delGrupo->take($mitad)->values(),
                    'derecha'   => $delGrupo->slice($mitad)->reverse()->values(),
                ]];
            });

        return view('impresiones.catequistas', [
            'retiro'     => $retiro,
            'grupos'     => $grupos,
            'rectorados' => $rectorados,
            'titulo'     => 'Catequistas por mesa',
        ]);
    }



    //  Informes, problemáticas, evaluación de equipos y acta de la
    // ofrenda. Salen con el retiro y el rectorado ya escritos; lo demás
    // queda en blanco. El del dirigente trae las charlas del desarrollo
    // (o las de la base si todavía no se preparó) y el del rector, los
    // catequistas de cada mesa para anotar su hora de llegada.
    public function informe(Request $request, string $formato)
    {
        abort_unless(array_key_exists($formato, self::INFORMES), 404);

        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        $rectorados = $retiro->rectorados()->orderBy('orden')->get()->groupBy('sexo');

        $grupos = collect(['Masculino', 'Femenino'])
            ->when($sexo, fn ($sexos) => $sexos->filter(fn ($grupo) => $grupo === $sexo))
            ->mapWithKeys(fn ($grupo) => [$grupo => [
                'rectorado' => $rectorados->get($grupo, collect()),
                'charlas'   => in_array($formato, ['dirigente', 'charlas']) ? $this->charlasDe($retiro, $grupo) : collect(),
                'mesas'     => $formato === 'rector' ? $retiro->mesas()->where('sexo', $grupo)->orderBy('numero')->get() : collect(),
            ]]);

        return view('impresiones.informes.' . $formato, [
            'retiro'  => $retiro,
            'grupos'  => $grupos,
            'equipos' => self::EQUIPOS_SERVICIO,
            'titulo'  => self::INFORMES[$formato],
        ]);
    }



    //  Toda la carpeta de un cargo en una sola impresión: cada documento
    // se arma igual que suelto y aquí solo se juntan, con una sola barra.
    // Si el desarrollo no está preparado, sus hojas se saltan y se avisa.
    public function carpeta(Request $request, string $cargo)
    {
        abort_unless(array_key_exists($cargo, self::CARPETAS), 404);

        $retiro    = $this->retiroOFallar();
        $carpeta   = self::CARPETAS[$cargo];
        $partes    = [];
        $faltantes = [];

        foreach ($carpeta['documentos'] as $documento) {
            $vista = match ($documento) {
                'charlistas', 'sala' => app(DesarrolloController::class)->imprimir(Request::create('/', 'GET', ['formato' => $documento, 'sexo' => $request->sexo])),
                'catequistas'        => $this->catequistas($request),
                default              => $this->informe($request, $documento),
            };

            if (! $vista instanceof View) {
                $faltantes[] = $documento === 'sala' ? 'la rotación en sala' : 'la lista de charlistas';
                continue;
            }

            $partes[] = $vista->with('enCarpeta', true)->render();
        }

        return view('impresiones.carpeta', [
            'retiro'    => $retiro,
            'partes'    => $partes,
            'faltantes' => $faltantes,
            'titulo'    => $carpeta['nombre'] . ($request->sexo === 'mujeres' ? ' · Mujeres' : ($request->sexo === 'hombres' ? ' · Hombres' : '')),
        ]);
    }



    //  Las charlas en el orden del retiro, sin repetir (algunas se parten
    // en dos bloques en el desarrollo), con su charlista y los temas que
    // debe tocar según la evaluación de charlas.
    private function charlasDe(Retiro $retiro, string $sexo): Collection
    {
        $charlas = $retiro->actividades()
            ->where('sexo', $sexo)
            ->where('es_charla', true)
            ->orderBy('inicio')
            ->get();

        if ($charlas->isEmpty()) {
            $charlas = ActividadBase::where('sexo', $sexo)
                ->where('es_charla', true)
                ->get()
                ->sortBy(fn ($actividad) => $actividad->dia_relativo . $actividad->orden);
        }

        return $charlas->unique('nombre')->values()->map(fn ($charla) => [
            'nombre'    => $charla->nombre,
            'charlista' => $charla->charlista,
            'temas'     => $this->temasDe($charla->nombre),
        ]);
    }



    //  "SER HOMBRE / CASTIDAD MATRIMONIAL" -> los temas de "SER HOMBRE".
    // Se compara sin tildes ni signos de pregunta y gana la clave más
    // larga con la que empiece el nombre; si ninguna, no hay temas.
    private function temasDe(string $charla): array
    {
        $nombre = trim(Str::upper(Str::ascii(str_replace(['¿', '?'], '', $charla))));

        $clave = collect(config('evaluacion_charlas'))
            ->keys()
            ->filter(fn ($clave) => str_starts_with($nombre, $clave))
            ->sortByDesc(fn ($clave) => strlen($clave))
            ->first();

        return $clave ? config('evaluacion_charlas.' . $clave) : [];
    }



    //  Tarjetas "De / Para" de las flores, en blanco para llenar a mano
    // y recortar. En oficio caben 27 (3 x 9), como el Word de siempre; en
    // carta, 21 (3 x 7), del mismo tamaño.
    public function flores(Request $request)
    {
        $papel = $request->papel === 'carta' ? 'carta' : 'oficio';

        return view('impresiones.flores', [
            'retiro'   => $this->retiroOFallar(),
            'papel'    => $papel,
            'tarjetas' => $papel === 'carta' ? 21 : 27,
            'titulo'   => 'Tarjetas De / Para (' . $papel . ')',
        ]);
    }



    //  Rótulos para marcar los sobres de "Ayudas de Dios": uno por
    // persona, en orden de mesa y silla, para recortar y pegar.
    public function sobres(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.sobres', [
            'retiro' => $retiro,
            'grupos' => $this->inscritos($retiro, $sexo)->groupBy(fn ($inscrito) => $inscrito->persona->sexo),
            'titulo' => 'Rótulos para sobres',
        ]);
    }



    // Por mesa: cada invitado con sus contactos de emergencia
    public function llamadas(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.llamadas', [
            'retiro' => $retiro,
            'grupos' => $this->porGrupoYMesa($retiro, $sexo),
            'titulo' => 'Llamadas familiares',
        ]);
    }



    //  Letreros para pegar en la pared: una mesa por hoja carta, con el
    // logo; azul para hombres y vino para mujeres. ?mesas=1-12 o 1,3,5
    // elige cuáles; sin eso salen las mesas que ya existen (o de la 1 a
    // la 10 si todavía no hay ninguna).
    public function letrerosMesa(Request $request)
    {
        $retiro  = $this->retiroOFallar();
        $sexos   = $this->sexoPedido($request) ? [$this->sexoPedido($request)] : ['Masculino', 'Femenino'];
        $pedidas = $this->numerosPedidos((string) $request->mesas);

        // Un lugar en vez de mesas ("Rectorado"): un letrero por grupo
        $lugar = mb_substr(trim((string) $request->lugar), 0, 40);

        if ($lugar !== '') {
            return view('impresiones.letrerosMesa', [
                'retiro' => $retiro,
                'grupos' => collect($sexos)->mapWithKeys(fn ($sexo) => [$sexo => collect()]),
                'lugar'  => $lugar,
                'titulo' => 'Letrero: ' . $lugar,
            ]);
        }

        $grupos = collect($sexos)->mapWithKeys(function ($sexo) use ($retiro, $pedidas) {
            $existentes = $retiro->mesas()->where('sexo', $sexo)->orderBy('numero')->get()->keyBy('numero');
            $numeros    = $pedidas ?: ($existentes->isNotEmpty() ? $existentes->keys()->all() : range(1, 10));

            // Si la mesa existe se usa (por su nombre); si no, una de mentiras solo para imprimir
            $mesas = collect($numeros)->map(fn ($numero) => $existentes[$numero] ?? new Mesa(['numero' => $numero, 'sexo' => $sexo]));

            return [$sexo => $mesas];
        });

        return view('impresiones.letrerosMesa', [
            'retiro' => $retiro,
            'grupos' => $grupos,
            'lugar'  => null,
            'titulo' => 'Letreros de mesa',
        ]);
    }



    //  "1-5, 8" -> [1, 2, 3, 4, 5, 8]. Lo que no se entiende se ignora;
    // máximo 60 mesas para no generar miles de hojas por un error.
    private function numerosPedidos(string $texto): array
    {
        $numeros = [];

        foreach (preg_split('/[\s,;]+/', trim($texto), -1, PREG_SPLIT_NO_EMPTY) as $parte) {
            if (preg_match('/^(\d{1,3})-(\d{1,3})$/', $parte, $rango)) {
                $numeros = array_merge($numeros, range(min($rango[1], $rango[2]), max($rango[1], $rango[2])));
            } elseif (ctype_digit($parte)) {
                $numeros[] = (int) $parte;
            }
        }

        $numeros = array_values(array_unique(array_filter($numeros, fn ($numero) => $numero >= 1)));
        sort($numeros);

        return array_slice($numeros, 0, 60);
    }



    private function retiroOFallar(): Retiro
    {
        $retiro = Retiro::actual();
        abort_if(! $retiro, 404, 'No hay un retiro próximo.');

        return $retiro;
    }



    // ?sexo=hombres, ?sexo=mujeres o nada (los dos grupos)
    private function sexoPedido(Request $request): ?string
    {
        return match ($request->sexo) {
            'hombres' => 'Masculino',
            'mujeres' => 'Femenino',
            default   => null,
        };
    }



    //  Quienes siguen en la lista, en el orden de impresión:
    // primero hombres y luego mujeres; dentro, mesa 1 silla 1, 2, 3…,
    // luego mesa 2. Quien no tiene puesto queda al final de su grupo.
    private function inscritos(Retiro $retiro, ?string $sexo): Collection
    {
        return Inscripcion::with(['persona', 'mesa', 'contactosEmergencia'])
            ->select('inscripcions.*')
            ->where('inscripcions.retiro_id', $retiro->id)
            ->whereNotIn('inscripcions.estado', Inscripcion::ESTADOS_FUERA)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->leftJoin('mesas', 'mesas.id', '=', 'inscripcions.mesa_id')
            ->when($sexo, fn ($consulta) => $consulta->where('personas.sexo', $sexo))
            ->orderByRaw("personas.sexo = 'Femenino'")
            ->orderByRaw('inscripcions.mesa_id IS NULL')
            ->orderBy('mesas.numero')
            ->orderBy('inscripcions.silla')
            ->orderBy('personas.apellidos')
            ->get();
    }



    //  [ 'Masculino' => [mesa => [inscritos]], 'Femenino' => … ] con las
    // mesas en orden y los catequistas de cada una.
    private function porGrupoYMesa(Retiro $retiro, ?string $sexo): Collection
    {
        $mesas = $retiro->mesas()
            ->when($sexo, fn ($consulta) => $consulta->where('sexo', $sexo))
            ->orderBy('numero')
            ->get()
            ->groupBy('sexo');

        return $this->inscritos($retiro, $sexo)
            ->groupBy(fn ($inscrito) => $inscrito->persona->sexo)
            ->map(fn ($delGrupo, $sexoGrupo) => [
                'mesas'     => ($mesas[$sexoGrupo] ?? collect())->map(fn ($mesa) => [
                    'mesa'      => $mesa,
                    'inscritos' => $delGrupo->where('mesa_id', $mesa->id)->values(),
                ])->filter(fn ($fila) => $fila['inscritos']->isNotEmpty())->values(),
                'sinPuesto' => $delGrupo->whereNull('mesa_id')->values(),
            ]);
    }



    // El retiro que sigue a este, para la carta ("Nuestro próximo retiro…")
    private function proximoRetiro(Retiro $retiro): ?Retiro
    {
        return Retiro::whereDate('fecha_inicio', '>', $retiro->fecha_fin)
            ->orderBy('fecha_inicio')
            ->first();
    }
}
