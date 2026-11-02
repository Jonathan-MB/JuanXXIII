<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportarRetiristasRequest;
use App\Models\Inscripcion;
use App\Models\Persona;
use App\Models\RetiristaAnterior;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ZipArchive;

class HistorialController extends Controller
{

    //  Cómo se reconoce cada columna por su título (en minúscula y sin
    // tildes). Se toma la primera que coincida.
    private const COLUMNAS = [
        'cedula'    => ['cedula', 'documento', 'identificacion', 'cc', 'no documento', 'numero de documento', 'n documento'],
        'nombre'    => ['nombre completo', 'nombres y apellidos', 'nombre y apellidos', 'nombre'],
        'nombres'   => ['nombres'],
        'apellidos' => ['apellidos', 'apellido'],
        'sexo'      => ['sexo', 'genero'],
        'numero'    => ['retiro', 'numero retiro', 'numero de retiro', 'n retiro', 'no retiro', 'retiro no'],
        'anio'      => ['ano', 'anio', 'fecha', 'fecha retiro', 'fecha del retiro'],
        'celular'   => ['celular', 'telefono', 'movil', 'cel'],
    ];

    //  Si ningún título coincide exacto, basta con que contenga la palabra
    // («Número de Cédula de Ciudadanía»). Se saltan las columnas de
    // contactos y padrinos, que también dicen nombre y celular.
    private const PALABRAS = [
        'cedula'  => ['cedula', 'documento'],
        'nombre'  => ['nombre'],
        'sexo'    => ['sexo', 'genero'],
        'celular' => ['celular'],
    ];



    //  Busca a una persona por nombre, apellido o cédula y muestra en qué
    // retiros ha estado: cuál terminó (y con qué número) y cuáles no.
    // También busca en los registros de antes de la aplicación.
    public function index(Request $request)
    {
        $busqueda   = trim((string) $request->busqueda);
        $personas   = collect();
        $anteriores = collect();

        if (mb_strlen($busqueda) >= 3) {
            $cedula = Persona::normalizarCedula($busqueda);

            $personas = Persona::with(['inscripciones' => fn ($consulta) => $consulta->with(['retiro', 'mesa'])->latest()])
                ->where(function ($donde) use ($busqueda, $cedula) {
                    $donde->whereRaw("CONCAT(nombres, ' ', apellidos) LIKE ?", ['%' . $busqueda . '%'])
                        ->orWhere('cedula', 'like', '%' . $cedula . '%');
                })
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->limit(50)
                ->get();

            // Quien ya está en la aplicación sale arriba con sus datos completos
            $anteriores = RetiristaAnterior::where(function ($donde) use ($busqueda, $cedula) {
                    $donde->where('nombre', 'like', '%' . $busqueda . '%')
                        ->orWhere('cedula', 'like', '%' . $cedula . '%');
                })
                ->whereNotIn('cedula', $personas->pluck('cedula'))
                ->orderBy('nombre')
                ->limit(50)
                ->get();
        }

        $totales = [
            'retiristas' => Inscripcion::where('estado', Inscripcion::TERMINO)->distinct('persona_id')->count('persona_id'),
            'personas'   => Persona::count(),
            'anteriores' => RetiristaAnterior::count(),
        ];

        return view('historial.index', compact('busqueda', 'personas', 'anteriores', 'totales'));
    }



    //  Carga un Excel o CSV de retiros anteriores. Se puede repetir: una
    // cédula que ya estaba se actualiza en vez de duplicarse.
    public function importar(ImportarRetiristasRequest $request)
    {
        $archivo = $request->file('archivo');
        $hoja    = $request->validated('hoja');
        $filas   = strtolower($archivo->getClientOriginalExtension()) === 'xlsx'
            ? $this->leerExcel($archivo->getRealPath(), $hoja)
            : $this->leerCsv($archivo->getRealPath());

        if ($filas === null) {
            return back()->withInput()->with('error', 'El Excel no tiene una hoja llamada «' . $hoja . '».');
        }

        [$indice, $columnas] = $this->buscarTitulos($filas);

        if ($indice === null) {
            return back()->withInput()->with('error', 'No se encontró la fila de títulos: el archivo necesita una columna «Cédula» (o «Documento») y una de nombre.');
        }

        $nuevos       = 0;
        $actualizados = 0;
        $omitidos     = [];

        DB::transaction(function () use ($filas, $indice, $columnas, $request, &$nuevos, &$actualizados, &$omitidos) {
            foreach (array_slice($filas, $indice + 1, null, true) as $numeroFila => $fila) {
                $dato = fn ($campo) => isset($columnas[$campo]) ? trim((string) ($fila[$columnas[$campo]] ?? '')) : '';

                $cedula = Persona::normalizarCedula($dato('cedula'));
                $nombre = $dato('nombre') ?: trim($dato('nombres') . ' ' . $dato('apellidos'));

                // Fila vacía: no se cuenta
                if ($cedula === '' && $nombre === '') {
                    continue;
                }

                if ($cedula === '' || $nombre === '' || mb_strlen($cedula) > 20) {
                    $omitidos[] = $numeroFila + 1;
                    continue;
                }

                $retirista = RetiristaAnterior::firstOrNew(['cedula' => $cedula]);
                $existia   = $retirista->exists;

                $retirista->fill([
                    'nombre'        => Str::limit(Str::title(mb_strtolower($nombre)), 120, ''),
                    'sexo'          => $this->leerSexo($dato('sexo')) ?? $request->validated('sexo') ?? $retirista->sexo,
                    'numero_retiro' => $this->leerNumero($dato('numero')) ?? $request->validated('numero_retiro') ?? $retirista->numero_retiro,
                    'anio'          => $this->leerAnio($dato('anio')) ?? $request->validated('anio') ?? $retirista->anio,
                    'celular'       => Str::limit($dato('celular'), 20, '') ?: $retirista->celular,
                ]);

                if (! $existia) {
                    $retirista->save();
                    $nuevos++;
                } elseif ($retirista->isDirty()) {
                    $retirista->save();
                    $actualizados++;
                }
            }
        });

        $mensaje = 'Importación lista: ' . $nuevos . ' ' . ($nuevos === 1 ? 'retirista nuevo' : 'retiristas nuevos')
            . ($actualizados ? ' y ' . $actualizados . ' ' . ($actualizados === 1 ? 'actualizado' : 'actualizados') : '') . '.';

        if ($omitidos) {
            return back()->with('mensajeAlerta', $mensaje . ' Se saltaron ' . count($omitidos) . ' filas sin cédula o sin nombre (filas ' . implode(', ', array_slice($omitidos, 0, 15)) . (count($omitidos) > 15 ? '…' : '') . ').');
        }

        return back()->with('mensajeCreado', $mensaje);
    }



    //  La fila de títulos es la primera (de las 15 primeras) que tenga
    // cédula y algún nombre. Devuelve [índice, campo => columna].
    private function buscarTitulos(array $filas): array
    {
        foreach (array_slice($filas, 0, 15, true) as $indice => $fila) {
            $titulos  = array_map(fn ($titulo) => trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower(Str::ascii((string) $titulo)))), $fila);
            $columnas = [];

            foreach (self::COLUMNAS as $campo => $nombres) {
                foreach ($nombres as $nombre) {
                    $columna = array_search($nombre, $titulos, true);

                    if ($columna !== false && ! in_array($columna, $columnas, true)) {
                        $columnas[$campo] = $columna;
                        break;
                    }
                }
            }

            foreach (self::PALABRAS as $campo => $palabras) {
                if (isset($columnas[$campo])) {
                    continue;
                }

                foreach ($titulos as $columna => $titulo) {
                    if (in_array($columna, $columnas, true) || preg_match('/contacto|emergencia|padrino|correo/', $titulo)) {
                        continue;
                    }

                    if (Str::contains($titulo, $palabras)) {
                        $columnas[$campo] = $columna;
                        break;
                    }
                }
            }

            if (isset($columnas['cedula']) && (isset($columnas['nombre']) || isset($columnas['nombres']))) {
                return [$indice, $columnas];
            }
        }

        return [null, []];
    }



    //  CSV de Excel: separado por ";" o "," y a veces sin UTF-8.
    private function leerCsv(string $ruta): array
    {
        $texto = file_get_contents($ruta);
        $texto = preg_replace('/^\xEF\xBB\xBF/', '', $texto);

        if (! mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
        }

        $lineas    = preg_split('/\r\n|\r|\n/', $texto);
        $separador = substr_count($lineas[0] ?? '', ';') >= substr_count($lineas[0] ?? '', ',') ? ';' : ',';

        return array_map(fn ($linea) => str_getcsv($linea, $separador), $lineas);
    }



    //  Lee una hoja de un .xlsx sin librerías: es un zip con XML. Sin
    // nombre, la primera. Las celdas vacías no vienen en el XML, por eso se
    // ubican por su letra. Devuelve null si la hoja pedida no existe.
    private function leerExcel(string $ruta, ?string $nombreHoja): ?array
    {
        $zip = new ZipArchive();

        if ($zip->open($ruta) !== true) {
            return [];
        }

        $textos = [];

        if ($xml = $zip->getFromName('xl/sharedStrings.xml')) {
            foreach (simplexml_load_string($xml)->si as $texto) {
                $textos[] = trim(strip_tags($texto->asXML()));
            }
        }

        $libro   = simplexml_load_string($zip->getFromName('xl/workbook.xml'));
        $enlaces = simplexml_load_string($zip->getFromName('xl/_rels/workbook.xml.rels'));
        $elegida = $libro->sheets->sheet[0];

        if ($nombreHoja) {
            $elegida = null;

            foreach ($libro->sheets->sheet as $hojaLibro) {
                if (mb_strtolower(trim((string) $hojaLibro['name'])) === mb_strtolower(trim($nombreHoja))) {
                    $elegida = $hojaLibro;
                }
            }

            if (! $elegida) {
                return null;
            }
        }

        $primera = (string) $elegida->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
        $destino = 'worksheets/sheet1.xml';

        foreach ($enlaces->Relationship as $enlace) {
            if ((string) $enlace['Id'] === $primera) {
                $destino = ltrim(str_replace('/xl/', '', (string) $enlace['Target']), '/');
            }
        }

        $hoja  = simplexml_load_string($zip->getFromName('xl/' . $destino));
        $filas = [];

        foreach ($hoja->sheetData->row as $fila) {
            $valores = [];

            foreach ($fila->c as $celda) {
                $letras = preg_replace('/\d+/', '', (string) $celda['r']);
                $tipo   = (string) $celda['t'];
                $valor  = match ($tipo) {
                    's'         => $textos[(int) $celda->v] ?? '',
                    'inlineStr' => trim(strip_tags($celda->is->asXML())),
                    default     => (string) $celda->v,
                };

                $valores[$this->columnaDeLetras($letras)] = $valor;
            }

            $filas[(int) $fila['r'] - 1] = $valores ? array_replace(array_fill(0, max(array_keys($valores)) + 1, ''), $valores) : [];
        }

        $zip->close();

        return $filas;
    }



    private function columnaDeLetras(string $letras): int
    {
        $numero = 0;

        foreach (str_split($letras) as $letra) {
            $numero = $numero * 26 + (ord($letra) - 64);
        }

        return $numero - 1;
    }



    //  En los Excel del movimiento «M» es mujeres y «H» hombres
    // ('Retiro 89 M - 90 H'); «Masculino» escrito completo es hombre.
    private function leerSexo(string $valor): ?string
    {
        $inicial = strtoupper(substr(Str::ascii($valor), 0, 1));

        return match ($inicial) {
            'M'     => str_starts_with(Str::lower($valor), 'masc') ? 'Masculino' : 'Femenino',
            'H'     => 'Masculino',
            'F'     => 'Femenino',
            default => null,
        };
    }



    private function leerNumero(string $valor): ?int
    {
        return preg_match('/\d{1,3}/', $valor, $numero) ? (int) $numero[0] : null;
    }



    //  Un año escrito, una fecha en texto o una fecha de Excel (que es el
    // número de días desde 1900).
    private function leerAnio(string $valor): ?int
    {
        if (preg_match('/\b(19[6-9]\d|20\d\d)\b/', $valor, $anio)) {
            return (int) $anio[1];
        }

        if (is_numeric($valor) && $valor > 20000 && $valor < 80000) {
            return Carbon::create(1899, 12, 30)->addDays((int) $valor)->year;
        }

        return null;
    }
}
