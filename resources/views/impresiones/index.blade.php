@php
    // Las carpetas completas van en su propia sección, arriba
    $documentos = collect(\App\Support\CatalogoImpresiones::DOCUMENTOS)->reject(fn ($documento) => $documento["ruta"] === "impresiones.carpeta");
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

        {{-- --------------------CARPETAS DEL RECTORADO------------------- --}}
        {{-- Cada carpeta sale entera en una sola impresión, en el orden de la lista de formatos --}}
        <section class="seccion-carpetas">
            <h2 class="titulo-seccion-impresion">Carpetas del rectorado</h2>
            <p class="texto-ayuda">Cada botón abre la carpeta completa: primero el rótulo para el frente (nombre, cargo y número del retiro) y después todas sus hojas en orden, con las copias que lleva. Para descargarla: <strong>Imprimir</strong> y elegir <strong>Guardar como PDF</strong>.</p>
            <div class="rejilla-impresiones">
                @foreach (\App\Http\Controllers\ImpresionController::CARPETAS as $cargo => $carpeta)
                    <article class="tarjeta-impresion tarjeta-carpeta" id="{{ \Illuminate\Support\Str::slug($carpeta['nombre'] . ' completa') }}">
                        <h3 class="nombre-impresion">{{ $carpeta['nombre'] }}</h3>
                        <ol class="contenido-carpeta">
                            @foreach ($carpeta['documentos'] as $documento => $copias)
                                <li>
                                    {{ \App\Http\Controllers\ImpresionController::PARTES_CARPETA[$documento] }}
                                    @if ($copias > 1)<strong>({{ $copias }} copias)</strong>@endif
                                    @if ($documento === 'equipos')<small>solo uno de los dos vicerrectores</small>@endif
                                </li>
                            @endforeach
                        </ol>
                        <div class="botones-impresion">
                            <a class="boton-azul" href="{{ route('impresiones.carpeta', ['cargo' => $cargo, 'sexo' => 'hombres']) }}" target="_blank" rel="noopener">Hombres</a>
                            <a class="boton-azul" href="{{ route('impresiones.carpeta', ['cargo' => $cargo, 'sexo' => 'mujeres']) }}" target="_blank" rel="noopener">Mujeres</a>
                            <a class="boton-secundario" href="{{ route('impresiones.carpeta', ['cargo' => $cargo]) }}" target="_blank" rel="noopener">Ambos</a>
                        </div>
                        @if ($cargo === 'vicerrector')
                            <p class="segunda-carpeta">
                                Segundo vicerrector(a), sin evaluación de equipos:
                                <a href="{{ route('impresiones.carpeta', ['cargo' => $cargo, 'sexo' => 'hombres', 'equipos' => 0]) }}" target="_blank" rel="noopener">Hombres</a> ·
                                <a href="{{ route('impresiones.carpeta', ['cargo' => $cargo, 'sexo' => 'mujeres', 'equipos' => 0]) }}" target="_blank" rel="noopener">Mujeres</a>
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>

        <h2 class="titulo-seccion-impresion">Hojas sueltas</h2>

        {{-- --------------------DOCUMENTOS------------------- --}}
        <div class="rejilla-impresiones">
            @foreach ($documentos as $documento)
                <article class="tarjeta-impresion" id="{{ \Illuminate\Support\Str::slug($documento['nombre']) }}">
                    <h2 class="nombre-impresion">{{ $documento['nombre'] }}</h2>
                    <p class="cuando-impresion">{{ $documento['cuando'] }}</p>
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

            {{-- Papel para cartas a mano: no depende del grupo ni del retiro --}}
            <article class="tarjeta-impresion" id="hoja-para-cartas">
                <h2 class="nombre-impresion">Hoja para cartas</h2>
                <p class="cuando-impresion">Cuando se necesite · cartas a mano</p>
                <p class="descripcion-impresion">Hoja carta bonita y suave para escribir cartas a mano: el logo arriba, el logo grande como marca de agua muy clara y ramitas de olivo. Gasta poca tinta.</p>
                <div class="botones-impresion">
                    <a class="boton-azul" href="{{ route('impresiones.hojaCarta') }}" target="_blank" rel="noopener">Con renglones</a>
                    <a class="boton-azul" href="{{ route('impresiones.hojaCarta', ['renglones' => 0]) }}" target="_blank" rel="noopener">Sin renglones</a>
                </div>
            </article>

            {{-- Tarjetas en blanco: no dependen del grupo sino del papel --}}
            <article class="tarjeta-impresion">
                <h2 class="nombre-impresion">Tarjetas De / Para (flores)</h2>
                <p class="cuando-impresion">Antes del retiro · para Floristería</p>
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
