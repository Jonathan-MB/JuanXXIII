<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardarFirmantesRequest;
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

    //  Las hojas que pueden ir en una carpeta, con el nombre que se ve en
    // la página Impresiones. «desarrollo», «charlistas» y «sala» salen del
    // desarrollo del retiro; «llamado» y «catequistas» son listas de aquí;
    // las demás son los informes.
    public const PARTES_CARPETA = [
        'desarrollo'    => 'Desarrollo del retiro',
        'llamado'       => 'Relación de invitados (hoja de llamado)',
        'catequistas'   => 'Distribución en sala de catequistas (catequistas por mesa)',
        'sala'          => 'Rotación al Santísimo en sala',
        'charlistas'    => 'Lista de charlistas',
        'charlas'       => 'Evaluación de charlas',
        'ofrenda'       => 'Arqueo para la ofrenda',
        'problematicas' => 'Problemáticas',
        'equipos'       => 'Evaluación de equipos',
        'rector'        => 'Informe del rector(a) responsable',
        'vicerrector'   => 'Informe del vicerrector(a) responsable',
        'dirigente'     => 'Informe del dirigente responsable',
    ];

    //  Lo que lleva la carpeta de cada cargo, en el orden de la lista de
    // formatos del rectorado, con cuántas copias de cada hoja.
    public const CARPETAS = [
        'rector' => [
            'nombre'     => 'Carpeta del rector(a)',
            'documentos' => ['desarrollo' => 2, 'llamado' => 1, 'catequistas' => 1, 'sala' => 2, 'charlistas' => 1, 'ofrenda' => 1, 'rector' => 1],
        ],
        'vicerrector' => [
            'nombre'     => 'Carpeta del vicerrector(a)',
            'documentos' => ['desarrollo' => 1, 'problematicas' => 1, 'equipos' => 1, 'vicerrector' => 1],
        ],
        'dirigente' => [
            'nombre'     => 'Carpeta del dirigente responsable',
            'documentos' => ['desarrollo' => 1, 'catequistas' => 1, 'charlas' => 1, 'charlistas' => 1, 'dirigente' => 1],
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



    //  La carta al presbítero, una por página, solo para quienes
    // llegaron o terminaron y tienen puesto: quien abandonó, no asistió o
    // no tiene mesa y silla no recibe carta.
    public function carta(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        $inscritos = $this->inscritos($retiro, $sexo)
            ->whereIn('estado', [Inscripcion::LLEGO, Inscripcion::TERMINO])
            ->whereNotNull('mesa_id')
            ->values();

        return view('impresiones.carta', [
            'retiro'    => $retiro,
            'inscritos' => $inscritos,
            'proximo'   => $this->proximoRetiro($retiro),
            'firmantes' => Firmante::orderBy('orden')->get(),
            'titulo'    => 'Cartas al presbítero',
        ]);
    }



    //  Membrete en hoja oficio para las dinámicas: uno por invitado, con el
    // número, las fechas y el año del retiro
    public function membretes(Request $request)
    {
        return $this->hojasPorInvitado($request, 'impresiones.membretes', 'Membretes oficio');
    }



    //  Tabla de los sacramentos para la dinámica del sábado: una por
    // invitado, dos por hoja oficio horizontal para cortar
    public function sacramentos(Request $request)
    {
        return $this->hojasPorInvitado($request, 'impresiones.sacramentos', 'Sacramentos (dinámica)');
    }



    //  Invocación al Espíritu Santo, Padre Nuestro y Credo: una por
    // invitado, dos por hoja carta para recortar
    public function oraciones(Request $request)
    {
        return $this->hojasPorInvitado($request, 'impresiones.oraciones', 'Oraciones');
    }



    //  Entrega de previstos (formato de Secretaría): por mesa, casillas
    // para lo que recibe cada persona. Sin mesas todavía, sale en blanco.
    public function previstos(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.previstos', [
            'retiro' => $retiro,
            'sexos'  => $sexo ? [$sexo] : ['Masculino', 'Femenino'],
            'grupos' => $this->porGrupoYMesa($retiro, $sexo),
            'titulo' => 'Entrega de previstos',
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

        return view('impresiones.llegada', [
            'retiro' => $retiro,
            'grupos' => $this->enOrdenAlfabetico($retiro, $this->sexoPedido($request)),
            'titulo' => 'Lista de llegada',
        ]);
    }



    //  Lista de llamado ("relación de invitados" de la carpeta del
    // rector(a)): con ella se llama a cada invitado a sala. Va antes de
    // dar las mesas, así que salen todos los de la lista en orden
    // alfabético; quien no llegó simplemente no responde.
    public function llamado(Request $request)
    {
        $retiro = $this->retiroOFallar();

        return view('impresiones.llamado', [
            'retiro' => $retiro,
            'grupos' => $this->enOrdenAlfabetico($retiro, $this->sexoPedido($request)),
            'titulo' => 'Lista de llamado',
        ]);
    }



    //  Hojas para que cada persona escriba sus datos en su silla. Usa las
    // mesas que ya existen; si no hay ninguna, 10 mesas de 8 sillas.
    //  Hoja 1: cada persona escribe su nombre en la fila de su silla
    public function nombresMesa(Request $request)
    {
        return $this->mesasEnBlanco($request, 'impresiones.nombresMesa', 'Hoja 1 · Nombres por mesa');
    }



    //  Hoja 2: datos de contacto en blanco, por mesa
    public function mesasEnBlanco(Request $request, string $vista = 'impresiones.mesasEnBlanco', string $titulo = 'Hoja 2 · Datos por mesa')
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request) ?? 'Masculino';

        $mesas = $retiro->mesas()->where('sexo', $sexo)->orderBy('numero')->get();

        if ($mesas->isEmpty()) {
            $mesas = collect(range(1, 10))->map(fn ($numero) => new Mesa(['numero' => $numero, 'cantidad_sillas' => 8, 'sexo' => $sexo]));
        }

        return view($vista, [
            'retiro' => $retiro,
            'sexo'   => $sexo,
            'mesas'  => $mesas,
            'titulo' => $titulo,
        ]);
    }



    //  Para Librería: cuántas camisetas de cada talla y, por mesa, la
    // talla de cada persona (así se reparten en los sobres).
    public function camisetas(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        // Solo quienes tienen puesto: los demás no están en el retiro
        $conteos = $this->inscritos($retiro, $sexo)
            ->whereNotNull('mesa_id')
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



    //  Listado de invitados para las puertas: por grupo, mesa y silla,
    // con ciudad, barrio y padrino. Letra grande para leerlo de pie;
    // puede ocupar varias hojas.
    public function puertas(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.puertas', [
            'retiro' => $retiro,
            'grupos' => $this->porGrupoYMesa($retiro, $sexo),
            'titulo' => 'Listado de invitados para puertas',
        ]);
    }



    //  Lista para familiares (horizontal): por grupo y por mesa, con
    // espacio para anotar si el invitado vino con otro invitado (de qué
    // mesa, silla y grupo), con quién se junta en la sorpresa final, si
    // llegó su familia y si necesita acompañamiento.
    public function familiares(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.familiares', [
            'retiro' => $retiro,
            'grupos' => $this->porGrupoYMesa($retiro, $sexo),
            'titulo' => 'Lista para familiares',
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
    // se arma igual que suelto y aquí solo se juntan (las hojas que van
    // dos veces salen repetidas), con una sola barra. Si el desarrollo no
    // está preparado, sus hojas se saltan y se avisa.
    //  ?equipos=0: carpeta del segundo vicerrector(a), sin la evaluación
    // de equipos (solo la lleva uno de los dos).
    public function carpeta(Request $request, string $cargo)
    {
        abort_unless(array_key_exists($cargo, self::CARPETAS), 404);

        $retiro     = $this->retiroOFallar();
        $carpeta    = self::CARPETAS[$cargo];
        $sinEquipos = $request->query('equipos') === '0';
        $partes     = [];
        $faltantes  = [];

        foreach ($carpeta['documentos'] as $documento => $copias) {
            if ($documento === 'equipos' && $sinEquipos) {
                continue;
            }

            $vista = match ($documento) {
                'desarrollo', 'charlistas', 'sala' => app(DesarrolloController::class)->imprimir(Request::create('/', 'GET', ['formato' => $documento, 'sexo' => $request->sexo])),
                'llamado'                          => $this->llamado($request),
                'catequistas'                      => $this->catequistas($request),
                default                            => $this->informe($request, $documento),
            };

            if (! $vista instanceof View) {
                $faltantes[] = mb_strtolower(self::PARTES_CARPETA[$documento]);
                continue;
            }

            $hoja = $vista->with('enCarpeta', true)->render();

            for ($copia = 0; $copia < $copias; $copia++) {
                $partes[] = $hoja;
            }
        }

        $grupo = match ($request->sexo) {
            'mujeres' => ' · Mujeres',
            'hombres' => ' · Hombres',
            default   => '',
        };

        //  Rótulo para el frente de la carpeta: el nombre de quien la lleva, su cargo y el número del retiro (uno por grupo)
        $sexos     = match ($request->sexo) {
            'mujeres' => ['Femenino'],
            'hombres' => ['Masculino'],
            default   => ['Masculino', 'Femenino'],
        };
        $cargoRectorado = ['rector' => 'Rector(a)', 'vicerrector' => 'Vicerrector(a)', 'dirigente' => 'Dirigente responsable'][$cargo];
        $rotulos = collect($sexos)->map(fn ($sexo) => [
            'nombre' => $retiro->rectorados()->where('sexo', $sexo)->where('cargo', $cargoRectorado)->value('nombre'),
            'cargo'  => match ($cargo) {
                'rector'      => $sexo === 'Femenino' ? 'Rectora' : 'Rector',
                'vicerrector' => $sexo === 'Femenino' ? 'Vicerrectora' : 'Vicerrector',
                default       => 'Dirigente responsable',
            },
            'numero' => $retiro->numeroPara($sexo),
        ]);

        return view('impresiones.carpeta', [
            'retiro'    => $retiro,
            'partes'    => $partes,
            'rotulos'   => $rotulos,
            'faltantes' => $faltantes,
            'titulo'    => $carpeta['nombre'] . ($sinEquipos ? ' 2' : '') . $grupo,
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
    // persona con puesto, en orden de mesa y silla, para recortar y pegar.
    public function sobres(Request $request)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);

        return view('impresiones.sobres', [
            'retiro' => $retiro,
            'grupos' => $this->inscritos($retiro, $sexo)->whereNotNull('mesa_id')->groupBy(fn ($inscrito) => $inscrito->persona->sexo),
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



    //  Hojas que van una por invitado. Cuántas: las que se pidan en
    // ?cantidad o, si no, los invitados que siguen en el retiro (inscritos,
    // confirmados y los que llegaron), por grupo.
    private function hojasPorInvitado(Request $request, string $vista, string $titulo)
    {
        $retiro = $this->retiroOFallar();
        $sexo   = $this->sexoPedido($request);
        $grupos = [];

        foreach ($sexo ? [$sexo] : ['Masculino', 'Femenino'] as $grupo) {
            $grupos[$grupo] = $sexo && $request->filled('cantidad')
                ? max(1, min(300, (int) $request->cantidad))
                : $this->invitadosActivos($retiro, $grupo);
        }

        return view($vista, compact('retiro', 'sexo', 'grupos', 'titulo'));
    }



    private function invitadosActivos(Retiro $retiro, string $sexo): int
    {
        $cantidad = $retiro->inscripciones()
            ->whereIn('estado', [Inscripcion::INSCRITO, Inscripcion::CONFIRMADO, Inscripcion::LLEGO])
            ->whereHas('persona', fn ($persona) => $persona->where('sexo', $sexo))
            ->count();

        return max($cantidad, 1);
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
            ->orderBy('personas.nombres')
            ->orderBy('personas.apellidos')
            ->get();
    }



    //  Quienes siguen en la lista, por grupo y por apellido, tengan o no
    // puesto: para las listas de antes de dar las mesas (llegada y llamado).
    private function enOrdenAlfabetico(Retiro $retiro, ?string $sexo): Collection
    {
        return Inscripcion::with('persona')
            ->select('inscripcions.*')
            ->where('inscripcions.retiro_id', $retiro->id)
            ->whereNotIn('inscripcions.estado', Inscripcion::ESTADOS_FUERA)
            ->join('personas', 'personas.id', '=', 'inscripcions.persona_id')
            ->when($sexo, fn ($consulta) => $consulta->where('personas.sexo', $sexo))
            ->orderByRaw("personas.sexo = 'Femenino'")
            ->orderBy('personas.nombres')
            ->orderBy('personas.nombres')
            ->orderBy('personas.apellidos')
            ->get()
            ->groupBy(fn ($inscrito) => $inscrito->persona->sexo);
    }



    //  [ 'Masculino' => [mesa => [inscritos]], 'Femenino' => … ] con las
    // mesas en orden y los catequistas de cada una. Quien no tiene mesa y
    // silla no asistió al retiro, así que no sale en ninguna lista.
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
                'mesas' => ($mesas[$sexoGrupo] ?? collect())->map(fn ($mesa) => [
                    'mesa'      => $mesa,
                    'inscritos' => $delGrupo->where('mesa_id', $mesa->id)->values(),
                ])->filter(fn ($fila) => $fila['inscritos']->isNotEmpty())->values(),
            ])
            ->filter(fn ($grupo) => $grupo['mesas']->isNotEmpty());
    }



    // El retiro que sigue a este, para la carta ("Nuestro próximo retiro…")
    private function proximoRetiro(Retiro $retiro): ?Retiro
    {
        // El marcado como próximo en Retiros o, si no hay, el siguiente por fecha
        return $retiro->siguiente();
    }
}
