@php
    $documentos = [
        [
            'ruta'        => 'impresiones.llegada',
            'nombre'      => 'Lista de llegada',
            'descripcion' => 'En orden alfabético: nombre, documento, edad, padrino y una casilla para marcar quién llegó.',
        ],
        [
            'ruta'        => 'impresiones.llamado',
            'nombre'      => 'Lista de llamado',
            'descripcion' => 'La relación de invitados del rector(a): en orden alfabético y con el nombre grande, para llamar a cada uno a sala y marcar quién entró.',
        ],
        [
            'ruta'        => 'impresiones.nombresMesa',
            'nombre'      => 'Hoja 1 · Nombres por mesa',
            'descripcion' => 'En blanco, dos mesas por hoja: cada persona escribe su nombre en la fila de su silla. Con ella se revisa que los nombres estén bien en la aplicación.',
            'soloGrupo'   => true,
        ],
        [
            'ruta'        => 'impresiones.mesasEnBlanco',
            'nombre'      => 'Hoja 2 · Datos por mesa',
            'descripcion' => 'En blanco, una mesa por hoja horizontal: silla, nombre, celular, ciudad, barrio y dos contactos de emergencia. Se corrige en Llegada con «Editar datos».',
            'soloGrupo'   => true,
        ],
        [
            'ruta'        => 'impresiones.correcciones',
            'nombre'      => 'Hoja 3 · Correcciones',
            'descripcion' => 'Ya impresa con los datos corregidos, por mesa: silla, nombre, documento, fecha de nacimiento, ciudad, barrio, celular y contactos de emergencia. La última revisión.',
        ],
        [
            'ruta'        => 'impresiones.membretes',
            'nombre'      => 'Membretes oficio (dinámicas)',
            'descripcion' => 'Hoja oficio con el logo, el número y las fechas del retiro arriba, para las dinámicas. Una por invitado; se entrega con una hoja en blanco más.',
        ],
        [
            'ruta'        => 'impresiones.oraciones',
            'nombre'      => 'Oraciones',
            'descripcion' => 'Invocación al Espíritu Santo, Padre Nuestro y Credo con el número, el lugar y la fecha del retiro. Una por invitado, dos por hoja carta para recortar.',
        ],
        [
            'ruta'        => 'impresiones.carta',
            'nombre'      => 'Cartas al presbítero',
            'descripcion' => 'Al final del retiro, una por página para cada persona que llegó y terminó (no a quien se fue). Por hombres y mujeres, en orden de mesa y silla.',
        ],
        [
            'ruta'        => 'impresiones.directorio',
            'nombre'      => 'Directorio',
            'descripcion' => 'Rectorado arriba, cada mesa con CT, CA y sus sillas, y las perseverancias al pie.',
        ],
        [
            'ruta'        => 'impresiones.ubicacion',
            'nombre'      => 'Hojas de ubicación',
            'descripcion' => 'Una hoja por mesa con el CA arriba, el CT abajo y las sillas alrededor, con el nombre de cada persona.',
        ],
        [
            'ruta'        => 'impresiones.previstos',
            'nombre'      => 'Entrega de previstos',
            'descripcion' => 'Por mesa, con CT y CA: casillas para camiseta (con la talla), crucifijo, camándula, cartas y previstos; falda solo en mujeres. Sin mesas, sale en blanco.',
        ],
        [
            'ruta'        => 'impresiones.camisetas',
            'nombre'      => 'Camisetas por talla',
            'descripcion' => 'Para Librería: cuántas hay de cada talla y la talla de cada persona por mesa.',
        ],
        [
            'ruta'        => 'impresiones.sobres',
            'nombre'      => 'Rótulos para sobres',
            'descripcion' => 'Diez por hoja, para recortar y pegar en los sobres de «Ayudas de Dios»: nombre, mesa y silla.',
        ],
        [
            'ruta'        => 'impresiones.llamadas',
            'nombre'      => 'Llamadas familiares',
            'descripcion' => 'Por mesa: cada invitado con sus contactos de emergencia, parentesco, teléfonos y una casilla para marcar la llamada.',
        ],
        [
            'ruta'        => 'impresiones.puertas',
            'nombre'      => 'Listado de invitados para puertas',
            'descripcion' => 'Hombres y mujeres por separado, por mesa y en orden de silla: nombre, ciudad, barrio y padrino. Letra grande, fácil de leer.',
        ],
        [
            'ruta'        => 'impresiones.familiares',
            'nombre'      => 'Lista para familiares',
            'descripcion' => 'Horizontal, por mesa, para llenar a lapicero: si vino la familia y, si el invitado vino con otro invitado, si es hombre o mujer y su mesa y silla.',
        ],
        [
            'ruta'        => 'impresiones.catequistas',
            'nombre'      => 'Catequistas por mesa',
            'descripcion' => 'Carpetas del rectorado: cada mesa con su CT y su CA, ubicadas en U como en la sala. Si aún no hay mesas, salen en blanco.',
        ],
        [
            'ruta'        => 'desarrollo.imprimir',
            'parametros'  => ['formato' => 'charlistas'],
            'nombre'      => 'Lista de charlistas',
            'descripcion' => 'Carpetas del rectorado: las charlas de cada día con su hora y el charlista escrito en el Desarrollo del retiro.',
        ],
        [
            'ruta'        => 'desarrollo.imprimir',
            'parametros'  => ['formato' => 'sala'],
            'nombre'      => 'Rotación al Santísimo en sala',
            'descripcion' => 'Carpeta del rector(a), dos copias: franjas de una hora durante las charlas, con espacio para escribir el catequista.',
        ],
        [
            'ruta'        => 'impresiones.carpeta',
            'parametros'  => ['cargo' => 'rector'],
            'nombre'      => 'Carpeta del rector(a) completa',
            'descripcion' => 'Todo junto en una sola impresión: charlistas, rotación en sala, catequistas por mesa, informe del rector(a), problemáticas y acta de la ofrenda.',
        ],
        [
            'ruta'        => 'impresiones.carpeta',
            'parametros'  => ['cargo' => 'vicerrector'],
            'nombre'      => 'Carpeta del vicerrector(a) completa',
            'descripcion' => 'Todo junto en una sola impresión: charlistas, informe del vicerrector(a) y evaluación de equipos.',
        ],
        [
            'ruta'        => 'impresiones.carpeta',
            'parametros'  => ['cargo' => 'dirigente'],
            'nombre'      => 'Carpeta del dirigente completa',
            'descripcion' => 'Todo junto en una sola impresión: charlistas, informe del dirigente responsable y evaluación de charlas.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'dirigente'],
            'nombre'      => 'Informe del dirigente responsable',
            'descripcion' => 'Carpeta del dirigente: cada charla del desarrollo con renglones para comentarla, la clausura y las observaciones.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'rector'],
            'nombre'      => 'Informe del rector(a)',
            'descripcion' => 'Catequistas que no asistieron, la llegada de cada CT, CA y CR por mesa, colaboraciones, sacerdotes y lo que se adjunta.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'vicerrector'],
            'nombre'      => 'Informe del vicerrector(a)',
            'descripcion' => 'Quienes no asistieron a sus equipos, la llegada del responsable de cada equipo y el accionar de los equipos (precios y servicio).',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'equipos'],
            'nombre'      => 'Evaluación de equipos',
            'descripcion' => 'Carpeta del vicerrector(a): una tabla por equipo con el nombre de cada dirigente, su hora de llegada y observaciones.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'problematicas'],
            'nombre'      => 'Problemáticas',
            'descripcion' => 'Hoja numerada del 1 al 20 para anotar la problemática de cada invitado y su mesa.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'ofrenda'],
            'nombre'      => 'Acta de entrega de la ofrenda',
            'descripcion' => 'Arqueo de billetes y monedas con el rectorado ya escrito, para entregar la ofrenda al tesorero(a) con las firmas.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'charlas'],
            'nombre'      => 'Evaluación de charlas',
            'descripcion' => 'Una hoja por charla con el charlista ya escrito: horario, reto, previsto, doctrina, los temas que debe tocar para marcar SI / NO y la calificación.',
        ],
    ];
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/impresiones.css') }}">
<title>Impresiones · Juan XXIII</title>

@include('partials.header')

    @if (! $retiro)
        <h1 class="vista-titulo">Impresiones</h1>
        <p class="texto-vacio">No hay un retiro próximo.</p>
    @else
        <div class="cabecera-vista">
            <h1 class="vista-titulo">Impresiones · {{ $retiro->nombre }}</h1>
            <a class="boton-secundario" href="{{ route('impresiones.datos') }}">Datos para imprimir</a>
        </div>
        @include('partials.mensaje')

        {{-- --------------------AVISOS------------------- --}}
        @php
            $pendientes = collect([
                $avisos['sinPuesto'] > 0      ? $avisos['sinPuesto'] . ' ' . ($avisos['sinPuesto'] === 1 ? 'persona no tiene' : 'personas no tienen') . ' mesa y silla: no salen en las listas (quien no tiene puesto no está en el retiro).' : null,
                $avisos['cartas'] === 0       ? 'Nadie tiene marcada la llegada todavía, así que no hay cartas para imprimir.' : null,
                $avisos['rectoradoH'] === 0   ? 'Falta el rectorado de hombres (va en el directorio).' : null,
                $avisos['rectoradoM'] === 0   ? 'Falta el rectorado de mujeres (va en el directorio).' : null,
                $avisos['mesasSinCt'] > 0     ? $avisos['mesasSinCt'] . ' ' . ($avisos['mesasSinCt'] === 1 ? 'mesa no tiene' : 'mesas no tienen') . ' CT escrito.' : null,
                $avisos['firmantes'] === 0    ? 'La carta no tiene firmas configuradas.' : null,
                ! $avisos['proximoRetiro']    ? 'No hay un próximo retiro creado: en la carta, la línea del próximo retiro sale con espacios en blanco.' : null,
            ])->filter();
        @endphp

        @if ($pendientes->isNotEmpty())
            <section class="avisos-impresion">
                <p class="titulo-avisos">Antes de imprimir, revisa:</p>
                <ul>
                    @foreach ($pendientes as $pendiente)
                        <li>{{ $pendiente }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- --------------------DOCUMENTOS------------------- --}}
        <div class="rejilla-impresiones">
            @foreach ($documentos as $documento)
                <article class="tarjeta-impresion">
                    <h2 class="nombre-impresion">{{ $documento['nombre'] }}</h2>
                    <p class="descripcion-impresion">{{ $documento['descripcion'] }}</p>
                    <div class="botones-impresion">
                        <a class="boton-azul" href="{{ route($documento['ruta'], ['sexo' => 'hombres'] + ($documento['parametros'] ?? [])) }}" target="_blank" rel="noopener">Hombres</a>
                        <a class="boton-azul" href="{{ route($documento['ruta'], ['sexo' => 'mujeres'] + ($documento['parametros'] ?? [])) }}" target="_blank" rel="noopener">Mujeres</a>
                        @unless ($documento['soloGrupo'] ?? false)
                            <a class="boton-secundario" href="{{ route($documento['ruta'], $documento['parametros'] ?? []) }}" target="_blank" rel="noopener">Ambos</a>
                        @endunless
                    </div>
                </article>
            @endforeach

            {{-- Tarjetas en blanco: no dependen del grupo sino del papel --}}
            <article class="tarjeta-impresion">
                <h2 class="nombre-impresion">Tarjetas De / Para (flores)</h2>
                <p class="descripcion-impresion">En blanco, para escribir a mano y recortar: De, Para, R, M y S, con el logo. Oficio: 27 por hoja. Carta: 21 por hoja.</p>
                <div class="botones-impresion">
                    <a class="boton-azul" href="{{ route('impresiones.flores', ['papel' => 'oficio']) }}" target="_blank" rel="noopener">Oficio</a>
                    <a class="boton-azul" href="{{ route('impresiones.flores', ['papel' => 'carta']) }}" target="_blank" rel="noopener">Carta</a>
                </div>
            </article>

            @include('impresiones.formularioLetreros')
        </div>
        <p class="texto-ayuda">Cada documento se abre en una pestaña nueva, listo para imprimir en hoja carta. Allí está el botón <strong>Imprimir</strong>.</p>
    @endif

@include('partials.footerPanel')
