<?php

namespace App\Support;

use App\Http\Controllers\LetreroController;
use App\Models\ActividadRetiro;
use App\Models\Archivo;
use App\Models\Articulo;
use App\Models\Equipo;
use App\Models\Inscripcion;
use App\Models\Perseverancia;
use App\Models\Persona;
use App\Models\Rectorado;
use App\Models\Retiro;
use App\Models\RetiristaAnterior;
use App\Models\Rol;
use App\Models\TareaCronograma;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

//  Buscador general: busca por nombre en todo lo de la aplicación
// (páginas, impresiones, letreros, archivos, personas, mesas, charlas,
// tareas, inventario...). Acepta palabras parecidas: sin tildes, en
// cualquier orden y con una o dos letras mal escritas.
//
//  Cada resultado dice si la cuenta lo puede abrir; si no, dice quién lo
// tiene (por ejemplo, «Lo tiene Secretaría») para saber a quién pedirlo.
class Buscador
{
    //  Los grupos de resultados (si empatan, se muestran en este orden)
    public const GRUPOS = [
        'paginas'        => 'Secciones de la aplicación',
        'impresiones'    => 'Impresiones',
        'letreros'       => 'Letreros',
        'archivos'       => 'Archivos subidos',
        'personas'       => 'Personas del retiro',
        'mesas'          => 'Mesas',
        'rectorado'      => 'Rectorado',
        'charlas'        => 'Charlas y actividades',
        'tareas'         => 'Lista de tareas',
        'inventario'     => 'Inventario',
        'perseverancias' => 'Perseverancias',
        'retiros'        => 'Retiros',
        'historial'      => 'Historial',
    ];

    //  Palabras que no ayudan a buscar
    private const VACIAS = ['de', 'del', 'la', 'las', 'el', 'los', 'y', 'o', 'a', 'al', 'en', 'para', 'por', 'con', 'un', 'una', 'que', 'se'];

    //  Las secciones de la aplicación con las palabras con que alguien
    // podría buscarlas: [nombre, ruta, parámetros, para qué sirve, palabras].
    private const PAGINAS = [
        ['Inicio', 'panel', [], 'La página principal con las tareas del día.', 'panel principal tareas hoy'],
        ['Inscritos', 'inscritos.index', [], 'Lista de inscritos del retiro: fichas, estados, pagos y datos de cada persona.', 'personas invitados ficha estado confirmar correo'],
        ['Pagos', 'pagos.index', [], 'Quién ha pagado, quién debe y cuánto falta por recoger.', 'tesoreria abono saldo dinero ofrenda deben plata cobrar'],
        ['Llegada', 'llegada.index', [], 'Marcar quién llegó, darle mesa y silla y corregir sus datos.', 'llegaron asistencia puesto silla corregir datos recibir'],
        ['Mesas', 'mesas.index', [], 'Las mesas de hombres y mujeres con su CT, CA y sillas.', 'sillas catequista titular auxiliar ct ca puestos'],
        ['Impresiones', 'impresiones.index', [], 'Todas las hojas para imprimir del retiro.', 'imprimir hojas listas papel formatos'],
        ['Datos para imprimir', 'impresiones.datos', [], 'Rectorado y firmas que salen en las hojas y en la carta.', 'rectorado firmas firmantes rector vicerrector dirigente'],
        ['Desarrollo del retiro', 'desarrollo.index', [], 'Las charlas y actividades de cada día con su hora y charlista.', 'charlas horario actividades charlista programa agenda'],
        ['Santísimo', 'santisimo.index', [], 'El horario de adoración al Santísimo de los equipos.', 'adoracion turnos horario capilla rotacion'],
        ['Documentos', 'archivos.index', ['categoria' => 'documentos'], 'Los documentos subidos (Word, Excel, PDF).', 'archivos word excel pdf subir'],
        ['Charlas (archivos)', 'archivos.index', ['categoria' => 'charlas'], 'Los archivos de las charlas.', 'archivos presentaciones diapositivas powerpoint'],
        ['Letreros', 'letreros.index', [], 'Los letreros para pegar en la casa, con vista previa.', 'avisos carteles pared'],
        ['Inventario', 'inventario.index', [], 'Lo que tiene cada equipo y lo que hay que comprar.', 'articulos comprar equipos cosas materiales conteo'],
        ['Lista de tareas', 'cronograma.index', [], 'Las tareas de Secretaría antes y durante el retiro.', 'cronograma pendientes que hacer'],
        ['Retiros', 'retiros.index', [], 'Crear el retiro, cambiar fechas y abrir o cerrar inscripciones.', 'fechas abrir cerrar inscripciones lugar ofrenda nuevo proximo'],
        ['Perseverancias', 'perseverancias.index', [], 'Los grupos de perseverancia que salen en el inicio.', 'cuarto dia grupos parroquias reuniones'],
        ['Historial', 'historial.index', [], 'Buscar si alguien ya hizo el retiro y en qué número.', 'retiristas anteriores ya hizo antiguos cedula'],
        ['Ajustes', 'ajustes.index', [], 'Grupo de WhatsApp, Nequi, correo de confirmación y código QR.', 'whatsapp nequi correo qr configuracion enlace'],
        ['Inscritos del retiro (Directivos)', 'directivos.index', [], 'La lista de inscritos para consultar e imprimir el directorio.', 'directivos lista consultar directorio'],
        ['Cuentas', 'usuarios.index', [], 'Crear cuentas y cambiar contraseñas.', 'usuarios contraseña clave acceso roles'],
        ['Descargar inscritos en Excel', 'exportar.inscritos', [], 'Respaldo de los inscritos en un archivo para Excel.', 'exportar excel respaldo descargar'],
        ['Descargar pagos en Excel', 'exportar.pagos', [], 'Respaldo de los pagos en un archivo para Excel.', 'exportar excel respaldo descargar tesoreria'],
        ['Formulario de inscripción', 'inscripcion', [], 'La página pública donde se inscriben los invitados.', 'inscribirse publico formulario enlace'],
    ];

    private Usuario $usuario;

    private ?Retiro $retiro;

    private array $buscadas = [];

    private array $permisos = [];



    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
        $this->retiro  = Retiro::actual();
    }



    //  Devuelve los resultados agrupados y si fueron solo parecidos
    // (cuando ninguno tenía todas las palabras).
    public function buscar(string $texto): array
    {
        $this->buscadas = self::palabrasBuscadas($texto);

        if ($this->buscadas === []) {
            return ['grupos' => collect(), 'parecidos' => false, 'total' => 0];
        }

        $todos     = $this->candidatos($texto);
        $parecidos = false;
        $hallados  = $this->puntuar($todos, true);

        if ($hallados->isEmpty() && count($this->buscadas) > 1) {
            $hallados  = $this->puntuar($todos, false);
            $parecidos = true;
        }

        $grupos = $hallados
            ->sortByDesc('puntos')
            ->groupBy('grupo')
            // Primero el grupo con lo más parecido; si empatan, en el orden de GRUPOS
            ->sortBy(fn ($lista, $grupo) => [-$lista->max('puntos'), array_search($grupo, array_keys(self::GRUPOS))]);

        return ['grupos' => $grupos, 'parecidos' => $parecidos, 'total' => $hallados->count()];
    }



    /*
    |--------------------------------------------------------------------------
    | Comparar palabras
    -------------------------------------------------------------------
    */

    //  En minúscula, sin tildes y solo letras y números
    public static function normalizar(?string $texto): string
    {
        $texto = Str::lower(Str::ascii((string) $texto));

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $texto));
    }



    private static function palabrasBuscadas(string $texto): array
    {
        $palabras = array_values(array_unique(array_filter(explode(' ', self::normalizar($texto)))));
        $utiles   = array_values(array_diff($palabras, self::VACIAS));

        return $utiles ?: $palabras;
    }



    //  Qué tanto se parece una palabra buscada a las palabras de un texto:
    // igual 10, empieza igual 7, la contiene 5, con letras de más o de
    // menos 3, y 0 si no se parece.
    private static function parecido(string $buscada, array $palabras, string $texto): int
    {
        $mejor   = 0;
        $esNumero = ctype_digit($buscada);
        $largo   = strlen($buscada);
        $permite = $largo >= 7 ? 2 : ($largo >= 4 ? 1 : 0);

        foreach ($palabras as $palabra) {
            // Igual, o la misma en plural o singular: «carta» y «cartas»
            if ($palabra === $buscada || $palabra === $buscada . 's' || $palabra === $buscada . 'es'
                || $buscada === $palabra . 's' || $buscada === $palabra . 'es') {
                return 10;
            }
            if ($esNumero) {
                continue; // «Mesa 1» no es «Mesa 12»
            }
            if (str_starts_with($palabra, $buscada)) {
                $mejor = max($mejor, 7);
            } elseif ($permite > 0 && abs(strlen($palabra) - $largo) <= $permite
                && levenshtein($buscada, $palabra) <= $permite) {
                $mejor = max($mejor, 3);
            } elseif ($largo >= 6 && strlen($palabra) > $largo && levenshtein($buscada, substr($palabra, 0, $largo)) <= 1) {
                $mejor = max($mejor, 3); // empieza casi igual: «camisets» → «camisetas»
            }
        }

        if ($mejor < 5 && ! $esNumero && $largo >= 3 && str_contains($texto, $buscada)) {
            $mejor = 5;
        }

        return $mejor;
    }



    //  Los títulos pesan el triple que lo demás. Con $todas, cada palabra
    // buscada tiene que aparecer en alguna parte.
    private function puntuar(Collection $candidatos, bool $todas): Collection
    {
        return $candidatos->map(function ($resultado) use ($todas) {
            $titulo   = self::normalizar($resultado['titulo']);
            $resto    = self::normalizar($resultado['detalle'] . ' ' . ($resultado['cuando'] ?? '') . ' ' . ($resultado['palabras'] ?? '') . ' ' . self::GRUPOS[$resultado['grupo']]);
            $enTitulo = explode(' ', $titulo);
            $enResto  = explode(' ', $resto);
            $puntos   = 0;
            $halladas = 0;

            foreach ($this->buscadas as $buscada) {
                $valor = max(self::parecido($buscada, $enTitulo, $titulo) * 3, self::parecido($buscada, $enResto, $resto));

                if ($valor === 0 && $todas) {
                    return null;
                }
                $puntos   += $valor;
                $halladas += $valor > 0 ? 1 : 0;
            }

            if ($halladas === 0) {
                return null;
            }

            //  Si el título es justo lo buscado, va de primero
            $junto = implode(' ', $this->buscadas);
            if ($titulo === $junto) {
                $puntos += 50;
            } elseif (str_starts_with($titulo, $junto . ' ')) {
                $puntos += 30;
            }

            //  Las hojas, letreros y secciones son lo que más se busca
            if (in_array($resultado['grupo'], ['impresiones', 'letreros', 'paginas'], true)) {
                $puntos += 8;
            }

            $resultado['puntos'] = $puntos;
            unset($resultado['palabras']);

            return $resultado;
        })->filter()->values();
    }



    /*
    |--------------------------------------------------------------------------
    | Permisos: si la cuenta puede abrir la ruta, y si no, quién la tiene
    -------------------------------------------------------------------
    */

    private function rolesDeRuta(string $nombre): ?array
    {
        $ruta = Route::getRoutes()->getByName($nombre);

        if (! $ruta) {
            return [];
        }

        $roles = null;
        foreach ($ruta->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'rol:')) {
                $estos = explode(',', substr($middleware, 4));
                $roles = $roles === null ? $estos : array_values(array_intersect($roles, $estos));
            }
        }

        return $roles; // null: cualquier cuenta
    }



    public function puede(string $nombre): bool
    {
        if (! array_key_exists($nombre, $this->permisos)) {
            $roles = $this->rolesDeRuta($nombre);
            $this->permisos[$nombre] = $roles === null || $this->usuario->tieneRol(...$roles);
        }

        return $this->permisos[$nombre];
    }



    //  «Lo tiene Secretaría», «Lo tienen Secretaría y Tesorería»
    private function quienLoTiene(string $nombre): string
    {
        $roles = array_values(array_diff($this->rolesDeRuta($nombre) ?? [], [Rol::ADMINISTRACION])) ?: [Rol::ADMINISTRACION];
        $lista = count($roles) > 1 ? implode(', ', array_slice($roles, 0, -1)) . ' y ' . end($roles) : $roles[0];

        return (count($roles) > 1 ? 'Lo tienen ' : 'Lo tiene ') . $lista;
    }



    //  Arma un resultado. Si la cuenta no puede abrir la ruta, prueba las
    // otras en orden; si ninguna sirve, dice quién lo tiene.
    private function resultado(string $grupo, string $titulo, string $detalle, array $rutas, string $palabras = '', ?string $cuando = null, bool $pestanaNueva = false): array
    {
        $url  = null;
        $nota = null;

        foreach ($rutas as [$ruta, $parametros]) {
            if (Route::has($ruta) && $this->puede($ruta)) {
                $url = route($ruta, $parametros);
                break;
            }
        }

        if (! $url && $rutas) {
            $nota = $this->quienLoTiene($rutas[0][0]);
        }

        return compact('grupo', 'titulo', 'detalle', 'url', 'nota', 'palabras', 'cuando', 'pestanaNueva');
    }



    /*
    |--------------------------------------------------------------------------
    | Todo lo que se puede encontrar
    -------------------------------------------------------------------
    */

    private function candidatos(string $texto): Collection
    {
        return collect()
            ->merge($this->paginas())
            ->merge($this->impresiones())
            ->merge($this->letreros())
            ->merge($this->archivos())
            ->merge($this->personas())
            ->merge($this->mesas())
            ->merge($this->rectorado())
            ->merge($this->charlas())
            ->merge($this->tareas())
            ->merge($this->inventario())
            ->merge($this->perseverancias())
            ->merge($this->retiros())
            ->merge($this->historial($texto));
    }



    private function paginas(): Collection
    {
        return collect(self::PAGINAS)
            // Directivos y Equipo no necesitan ver lo que nunca abrirán de Administración
            ->reject(fn ($pagina) => $pagina[1] === 'usuarios.index' && ! $this->usuario->tieneRol(Rol::ADMINISTRACION))
            ->map(fn ($pagina) => $this->resultado('paginas', $pagina[0], $pagina[3], [[$pagina[1], $pagina[2]]], $pagina[4]));
    }



    private function impresiones(): Collection
    {
        $lista = collect(CatalogoImpresiones::DOCUMENTOS)->map(function ($documento) {
            $parametros = $documento['parametros'] ?? [];
            $rutas      = [[$documento['ruta'], $parametros]];

            // Directivos imprime el directorio desde su propia página
            if ($documento['ruta'] === 'impresiones.directorio') {
                $rutas[] = ['directivos.directorio', $parametros];
            }

            $resultado = $this->resultado('impresiones', $documento['nombre'], $documento['descripcion'], $rutas, 'imprimir hoja', $documento['cuando'] ?? null, true);

            //  Se abre en la página Impresiones, donde se elige Hombres, Mujeres o Ambos
            if ($resultado['url'] && $this->puede('impresiones.index')) {
                $resultado['url']          = route('impresiones.index') . '#' . Str::slug($documento['nombre']);
                $resultado['pestanaNueva'] = false;
            }

            return $resultado;
        });

        $extras = [
            ['Tarjetas De / Para (flores)', 'En blanco, para escribir a mano y recortar: De, Para, R, M y S, con el logo.', 'impresiones.flores', ['papel' => 'carta'], 'Antes del retiro · para Floristería', 'floristeria tarjetas'],
            ['Letreros de número de mesa', 'Uno por hoja carta, con el logo, para cada mesa o para marcar un lugar («Rectorado»).', 'letreros.index', [], 'Antes del retiro · al arreglar la casa', 'letreros pared mesa numero'],
            ['Lista de pagos (quién debe)', 'Para Tesorería: cuánto ha pagado cada persona, quién debe y las cuentas al final.', 'pagos.imprimir', [], 'Antes y durante el retiro · Tesorería', 'pagos deben tesoreria saldo'],
            ['Inventario de todos los equipos', 'Una hoja por equipo para contar lo que hay y anotar lo que falta.', 'inventario.imprimirTodos', [], 'Antes y después del retiro', 'inventario conteo equipos'],
            ['Rotación al Santísimo de equipos', 'El horario de adoración de cada equipo para imprimir.', 'santisimo.imprimir', [], 'Antes del retiro', 'santisimo adoracion turnos'],
            ['Lista de tareas (cronograma)', 'Las tareas de Secretaría para marcar a mano.', 'cronograma.imprimir', [], 'Antes del retiro', 'cronograma tareas'],
            ['Desarrollo del retiro', 'Las charlas y actividades de cada día con su hora.', 'desarrollo.imprimir', [], 'Antes del retiro · carpetas', 'desarrollo charlas horario programa'],
        ];

        foreach ($extras as [$nombre, $descripcion, $ruta, $parametros, $cuando, $palabras]) {
            $lista->push($this->resultado('impresiones', $nombre, $descripcion, [[$ruta, $parametros]], $palabras, $cuando, true));
        }

        return $lista;
    }



    private function letreros(): Collection
    {
        return collect(LetreroController::LETREROS)->map(fn ($letrero, $clave) => $this->resultado(
            'letreros', $letrero[0], $letrero[1], [['letreros.show', $clave]], 'letrero aviso cartel', $letrero[2] ?? null, true,
        ))->values();
    }



    private function archivos(): Collection
    {
        return Archivo::all()->map(fn ($archivo) => $this->resultado(
            'archivos',
            trim(($archivo->numero ? $archivo->numero . '. ' : '') . $archivo->titulo),
            trim((Archivo::CATEGORIAS[$archivo->categoria] ?? $archivo->categoria) . ' · ' . $archivo->descripcion, ' ·'),
            [['archivos.show', $archivo], ['archivos.index', ['categoria' => $archivo->categoria]]],
            $archivo->nombre_original,
            null,
            true,
        ));
    }



    //  Las personas del retiro actual. Quien solo consulta (Directivos) no
    // ve a quien no asistió o se fue, igual que en su lista.
    private function personas(): Collection
    {
        if (! $this->retiro) {
            return collect();
        }

        $verFicha = $this->puede('inscritos.show');

        return Inscripcion::with(['persona', 'mesa'])
            ->where('retiro_id', $this->retiro->id)
            ->when(! $verFicha, fn ($consulta) => $consulta->whereNotIn('estado', Inscripcion::ESTADOS_FUERA))
            ->get()
            ->filter(fn ($inscrito) => $inscrito->persona)
            ->map(function ($inscrito) use ($verFicha) {
                $persona = $inscrito->persona;
                $grupo   = $persona->sexo === 'Femenino' ? 'mujeres' : 'hombres';
                $puesto  = $inscrito->mesa ? 'Mesa ' . $inscrito->mesa->numero . ($inscrito->silla ? ', silla ' . $inscrito->silla : '') : 'Sin mesa todavía';
                $detalle = collect([
                    Str::ucfirst($grupo),
                    $puesto,
                    $verFicha ? (Inscripcion::NOMBRES_ESTADO[$inscrito->estado] ?? null) : null,
                    $persona->ciudad,
                ])->filter()->implode(' · ');

                return $this->resultado('personas', $persona->nombre_completo, $detalle, [
                    ['inscritos.show', $inscrito],
                    ['directivos.index', ['sexo' => $grupo, 'busqueda' => $persona->nombres]],
                ], ($verFicha ? $persona->cedula . ' ' : '') . 'invitado ' . $inscrito->padrino_nombre);
            });
    }



    private function mesas(): Collection
    {
        if (! $this->retiro) {
            return collect();
        }

        return $this->retiro->mesas()->withCount('inscripciones')->orderBy('sexo')->orderBy('numero')->get()->map(function ($mesa) {
            $grupo = $mesa->sexo === 'Femenino' ? 'mujeres' : 'hombres';

            return $this->resultado(
                'mesas',
                'Mesa ' . $mesa->numero . ' · ' . Str::ucfirst($grupo) . ($mesa->nombre ? ' (' . $mesa->nombre . ')' : ''),
                collect(['CT: ' . ($mesa->catequista_titular ?: 'sin escribir'), 'CA: ' . ($mesa->catequista_auxiliar ?: 'sin escribir'), $mesa->inscripciones_count . ' personas'])->implode(' · '),
                [['mesas.index', ['sexo' => $grupo]], ['directivos.directorio', ['sexo' => $grupo]]],
                'mesa sillas catequista ' . $grupo,
            );
        });
    }



    private function rectorado(): Collection
    {
        if (! $this->retiro) {
            return collect();
        }

        return Rectorado::where('retiro_id', $this->retiro->id)->whereNotNull('nombre')->get()->map(fn ($cargo) => $this->resultado(
            'rectorado',
            $cargo->nombre,
            $cargo->cargo . ' · ' . ($cargo->sexo === 'Femenino' ? 'Mujeres' : 'Hombres') . ($cargo->ciudad ? ' · ' . $cargo->ciudad : ''),
            [['impresiones.datos', []], ['directivos.directorio', ['sexo' => $cargo->sexo === 'Femenino' ? 'mujeres' : 'hombres']]],
            'rectorado rector vicerrector dirigente',
        ));
    }



    private function charlas(): Collection
    {
        if (! $this->retiro) {
            return collect();
        }

        return ActividadRetiro::where('retiro_id', $this->retiro->id)->orderBy('inicio')->get()->map(function ($actividad) {
            $grupo = $actividad->sexo === 'Femenino' ? 'mujeres' : 'hombres';

            return $this->resultado(
                'charlas',
                $actividad->nombre,
                collect([
                    Str::ucfirst($grupo),
                    Str::ucfirst($actividad->inicio?->locale('es')->translatedFormat('l g:i a')),
                    $actividad->charlista ? 'Charlista: ' . $actividad->charlista : null,
                ])->filter()->implode(' · '),
                [['desarrollo.index', ['sexo' => $grupo]]],
                ($actividad->es_charla ? 'charla ' : 'actividad ') . $actividad->charlista,
            );
        });
    }



    private function tareas(): Collection
    {
        return TareaCronograma::orderBy('dia_relativo')->orderBy('hora')->get()->map(fn ($tarea) => $this->resultado(
            'tareas',
            $tarea->descripcion,
            collect([
                $tarea->dia_relativo < 0 ? abs($tarea->dia_relativo) . ' días antes del retiro' : 'Día ' . ($tarea->dia_relativo + 1) . ' del retiro',
                substr((string) $tarea->hora, 0, 5),
                $tarea->responsable,
            ])->filter()->implode(' · '),
            [['cronograma.index', []]],
            'tarea pendiente cronograma',
        ));
    }



    //  Artículos y equipos. Cada cuenta ve solo el inventario que puede abrir.
    private function inventario(): Collection
    {
        $equipos = Equipo::with('articulos')->orderBy('nombre')->get();

        return $equipos->flatMap(function ($equipo) {
            $puedeVer = $this->usuario->puedeVerInventario($equipo);
            $rutas    = $puedeVer ? [['inventario.show', $equipo]] : [];
            $nota     = $puedeVer ? null : 'Lo tiene el equipo ' . $equipo->nombre . ' y Secretaría';

            $lista = collect([array_merge($this->resultado('inventario', 'Equipo ' . $equipo->nombre, $equipo->articulos->count() . ' artículos en el inventario', $rutas, 'equipo inventario'), $puedeVer ? [] : ['nota' => $nota])]);

            foreach ($equipo->articulos as $articulo) {
                $lista->push(array_merge($this->resultado(
                    'inventario',
                    $articulo->nombre,
                    'Equipo ' . $equipo->nombre . ' · hay ' . Articulo::cantidadConUnidad($articulo->cantidad, $articulo->unidad)
                        . ($articulo->por_comprar ? ' · comprar ' . Articulo::cantidadConUnidad($articulo->por_comprar, $articulo->unidad) : ''),
                    $rutas,
                    'articulo ' . $articulo->estado,
                ), $puedeVer ? [] : ['nota' => $nota]));
            }

            return $lista;
        });
    }



    private function perseverancias(): Collection
    {
        return Perseverancia::all()->map(fn ($grupo) => $this->resultado(
            'perseverancias',
            $grupo->tipo . ' · ' . $grupo->sector,
            collect([$grupo->parroquia, trim($grupo->dia . ' ' . $grupo->hora), $grupo->direccion])->filter()->implode(' · '),
            [['perseverancias.edit', $grupo], ['inicio', []]],
            'perseverancia ' . $grupo->actividades,
        ));
    }



    private function retiros(): Collection
    {
        return Retiro::orderByDesc('fecha_inicio')->get()->map(fn ($retiro) => $this->resultado(
            'retiros',
            'Retiro ' . $retiro->numero_mujeres . ' M - ' . $retiro->numero_hombres . ' H',
            collect([
                $retiro->fecha_inicio?->locale('es')->translatedFormat('j \d\e F \d\e Y'),
                $retiro->lugar,
                $retiro->id === $this->retiro?->id ? 'retiro actual' : null,
            ])->filter()->implode(' · '),
            [['retiros.edit', $retiro]],
            'retiro ' . $retiro->numero_mujeres . ' ' . $retiro->numero_hombres . ' mujeres hombres ' . $retiro->ciudad,
        ));
    }



    //  Personas de otros retiros y de antes de la aplicación: un solo
    // resultado que lleva al Historial con la búsqueda hecha.
    private function historial(string $texto): Collection
    {
        $texto = trim($texto);

        if (mb_strlen($texto) < 3 || ! $this->puede('historial.index')) {
            return collect();
        }

        $parecido = '%' . str_replace(' ', '%', $texto) . '%';
        $cuantos  = Persona::whereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", [$parecido])
            ->orWhere('cedula', 'like', '%' . Persona::normalizarCedula($texto) . '%')
            ->count()
            + RetiristaAnterior::where('nombre', 'like', $parecido)->orWhere('cedula', 'like', '%' . $texto . '%')->count();

        if ($cuantos === 0) {
            return collect();
        }

        return collect([$this->resultado(
            'historial',
            'Buscar «' . $texto . '» en el historial',
            $cuantos . ($cuantos === 1 ? ' persona coincide' : ' personas coinciden') . ' en todos los retiros: mira en cuál estuvo cada una.',
            [['historial.index', ['busqueda' => $texto]]],
            implode(' ', $this->buscadas),
        )]);
    }
}
