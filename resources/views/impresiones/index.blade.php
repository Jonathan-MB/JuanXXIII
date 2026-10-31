@php
    $documentos = [
        [
            'ruta'        => 'impresiones.llegada',
            'nombre'      => 'Lista de llegada',
            'descripcion' => 'En orden alfabético: nombre, documento, edad, padrino y una casilla para marcar quién llegó.',
        ],
        [
            'ruta'        => 'impresiones.mesasEnBlanco',
            'nombre'      => 'Hojas de mesa en blanco',
            'descripcion' => 'Dos mesas por hoja con los números de silla, para que cada persona escriba su nombre, cédula y celular.',
            'soloGrupo'   => true,
        ],
        [
            'ruta'        => 'impresiones.carta',
            'nombre'      => 'Cartas al presbítero',
            'descripcion' => 'Una por página para cada persona que llegó al retiro. Separadas por hombres y mujeres, en orden de mesa y silla.',
        ],
        [
            'ruta'        => 'impresiones.correcciones',
            'nombre'      => 'Hoja de correcciones',
            'descripcion' => 'Por mesa: silla, nombre, ciudad, barrio, teléfono, fecha de nacimiento y contacto de emergencia, para corregir datos por última vez.',
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
            'ruta'        => 'impresiones.previstos',
            'nombre'      => 'Entrega de previstos',
            'descripcion' => 'Por mesa, con CT y CA: casillas para camiseta (con la talla), crucifijo, camándula, cartas y previstos; falda solo en mujeres.',
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
                $avisos['sinPuesto'] > 0      ? $avisos['sinPuesto'] . ' ' . ($avisos['sinPuesto'] === 1 ? 'persona no tiene' : 'personas no tienen') . ' mesa y silla: salen al final, sin puesto.' : null,
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

            {{-- Letreros para la pared: mesas elegidas o un lugar ("Rectorado") --}}
            <article class="tarjeta-impresion tarjeta-letreros">
                <h2 class="nombre-impresion">Letreros para la pared</h2>
                <p class="descripcion-impresion">Uno por hoja carta, con el logo: azul para hombres y vino para mujeres. Para mesas («Mesa 1», «Mesa 2»…) o para marcar un lugar («Rectorado»).</p>
                <form class="formulario-letreros" method="GET" action="{{ route('impresiones.letrerosMesa') }}" target="_blank">
                    <select name="sexo" aria-label="Grupo">
                        <option value="">Hombres y mujeres</option>
                        <option value="hombres">Hombres</option>
                        <option value="mujeres">Mujeres</option>
                    </select>
                    <input type="text" name="mesas" maxlength="60" placeholder="Mesas: 1-12 o 1, 3, 5 (vacío = todas)" aria-label="Mesas">
                    <input type="text" name="lugar" maxlength="40" list="lugares-letrero" placeholder="O un lugar: Rectorado…" aria-label="Lugar">
                    <datalist id="lugares-letrero">
                        <option value="Rectorado"></option>
                        <option value="Secretaría"></option>
                        <option value="Capilla"></option>
                        <option value="Baños"></option>
                        <option value="Comedor"></option>
                    </datalist>
                    <button type="submit" class="boton-azul">Ver letreros</button>
                </form>
            </article>
        </div>
        <p class="texto-ayuda">Cada documento se abre en una pestaña nueva, listo para imprimir en hoja carta. Allí está el botón <strong>Imprimir</strong>.</p>
    @endif

@include('partials.footerPanel')
