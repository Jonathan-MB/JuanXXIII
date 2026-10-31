@php
    $verTodos = auth()->user()->tieneRol(\App\Models\Rol::ADMINISTRACION, \App\Models\Rol::SECRETARIA);
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/inventario.css') }}">
<title>Inventario {{ $equipo->nombre }} · Juan XXIII</title>

@include('partials.header')

    @if ($verTodos)
        <a class="enlace-volver" href="{{ route('inventario.index') }}">← Todos los equipos</a>
    @endif
    <div class="cabecera-vista">
        <h1 class="vista-titulo">Inventario · {{ $equipo->nombre }}</h1>
        <a class="boton-secundario" href="{{ route('inventario.imprimir', $equipo) }}" target="_blank" rel="noopener">Imprimir</a>
    </div>
    @include('partials.mensaje')

    {{-- --------------------NUEVO ARTÍCULO------------------- --}}
    <details class="nuevo-articulo" {{ $errors->any() ? 'open' : '' }}>
        <summary class="boton-amarillo">+ Nuevo artículo</summary>
        <form class="formulario-articulo" method="POST" action="{{ route('inventario.store', $equipo) }}">
            @csrf
            <div class="elemento-formulario elemento-largo">
                <label class="titulo-elemento" for="nombreNuevo">Artículo</label>
                <input type="text" name="nombre" id="nombreNuevo" maxlength="80" value="{{ old('nombre') }}" placeholder="Ej.: Marcadores rojos" required>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="cantidadNueva">Cantidad</label>
                <input type="number" name="cantidad" id="cantidadNueva" min="0" inputmode="numeric" value="{{ old('cantidad', 0) }}" required>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="unidadNueva">Unidad</label>
                <input type="text" name="unidad" id="unidadNueva" maxlength="20" list="unidades" value="{{ old('unidad', 'unidades') }}" required>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="porComprarNuevo">Por comprar</label>
                <input type="number" name="porComprar" id="porComprarNuevo" min="0" inputmode="numeric" value="{{ old('porComprar', 0) }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="estadoNuevo">Estado <small>(opcional)</small></label>
                <input type="text" name="estado" id="estadoNuevo" maxlength="60" list="estados" value="{{ old('estado') }}">
            </div>
            <button type="submit" class="boton-azul">Agregar</button>
        </form>
    </details>

    <datalist id="unidades">
        <option value="unidades"></option>
        <option value="paquetes"></option>
        <option value="cajas"></option>
        <option value="resmas"></option>
        <option value="pliegos"></option>
        <option value="litros"></option>
        <option value="kilos"></option>
    </datalist>
    <datalist id="estados">
        <option value="Bueno"></option>
        <option value="Regular"></option>
        <option value="Dañado"></option>
    </datalist>

    {{-- --------------------BUSCAR Y FILTRAR------------------- --}}
    @if ($articulos->isNotEmpty())
        <div class="filtros-inventario">
            <input class="buscador" type="search" id="buscar-articulo" placeholder="Buscar artículo…" aria-label="Buscar artículo">
            <div class="interruptor-inventario" role="group" aria-label="Mostrar">
                <button type="button" class="opcion-filtro opcion-activa" data-filtro="todos">Todos ({{ $articulos->count() }})</button>
                <button type="button" class="opcion-filtro" data-filtro="comprar">Por comprar ({{ $porComprar }})</button>
            </div>
        </div>
    @endif

    {{-- --------------------ARTÍCULOS------------------- --}}
    @if ($articulos->isEmpty())
        <p class="texto-vacio">Este equipo todavía no tiene artículos. Agrega el primero con <strong>+ Nuevo artículo</strong>.</p>
    @else
        <p class="texto-vacio" id="sin-resultados" hidden>Ningún artículo coincide con la búsqueda.</p>
        <div class="lista-articulos">
            @foreach ($articulos as $articulo)
                <article class="tarjeta-articulo" data-nombre="{{ mb_strtolower($articulo->nombre) }}" data-comprar="{{ $articulo->por_comprar > 0 ? 1 : 0 }}">
                    <div class="cabecera-articulo">
                        <div>
                            <h2 class="nombre-articulo">{{ $articulo->nombre }}</h2>
                            <div class="chips-articulo">
                                @if ($articulo->estado)
                                    <span class="chip {{ in_array(mb_strtolower($articulo->estado), ['dañado', 'malo'], true) ? 'chip-rojo' : 'chip-gris' }}">{{ $articulo->estado }}</span>
                                @endif
                                @if ($articulo->por_comprar > 0)
                                    <span class="chip chip-amarillo">Comprar {{ $articulo->por_comprar }}</span>
                                @endif
                                @if ($articulo->cantidad === 0)
                                    <span class="chip chip-rojo">Agotado</span>
                                @endif
                            </div>
                        </div>
                        <p class="cantidad-articulo">{{ $articulo->cantidad }} <span>{{ $articulo->unidad }}</span></p>
                    </div>

                    <div class="movimientos-rapidos">
                        <details class="movimiento">
                            <summary class="boton-salida">− Salida</summary>
                            @include('inventario.movimiento', ['articulo' => $articulo, 'tipo' => 'salida', 'texto' => 'Sacar'])
                        </details>
                        <details class="movimiento">
                            <summary class="boton-entrada">+ Entrada</summary>
                            @include('inventario.movimiento', ['articulo' => $articulo, 'tipo' => 'entrada', 'texto' => 'Agregar'])
                        </details>
                    </div>

                    <details class="mas-articulo">
                        <summary>Editar, contar o ver historial</summary>

                        <form class="formulario-articulo" method="POST" action="{{ route('inventario.update', $articulo) }}">
                            @csrf
                            @method('PUT')
                            <div class="elemento-formulario elemento-largo">
                                <label class="titulo-elemento" for="nombre{{ $articulo->id }}">Artículo</label>
                                <input type="text" name="nombre" id="nombre{{ $articulo->id }}" maxlength="80" value="{{ $articulo->nombre }}" required>
                            </div>
                            <div class="elemento-formulario">
                                <label class="titulo-elemento" for="unidad{{ $articulo->id }}">Unidad</label>
                                <input type="text" name="unidad" id="unidad{{ $articulo->id }}" maxlength="20" list="unidades" value="{{ $articulo->unidad }}" required>
                            </div>
                            <div class="elemento-formulario">
                                <label class="titulo-elemento" for="comprar{{ $articulo->id }}">Por comprar</label>
                                <input type="number" name="porComprar" id="comprar{{ $articulo->id }}" min="0" inputmode="numeric" value="{{ $articulo->por_comprar }}">
                            </div>
                            <div class="elemento-formulario">
                                <label class="titulo-elemento" for="estado{{ $articulo->id }}">Estado</label>
                                <input type="text" name="estado" id="estado{{ $articulo->id }}" maxlength="60" list="estados" value="{{ $articulo->estado }}">
                            </div>
                            <button type="submit" class="boton-secundario">Guardar cambios</button>
                        </form>

                        <form class="formulario-movimiento formulario-ajuste" method="POST" action="{{ route('inventario.movimiento', $articulo) }}">
                            @csrf
                            <input type="hidden" name="tipo" value="ajuste">
                            <label class="titulo-elemento" for="ajuste{{ $articulo->id }}">Contamos y hay:</label>
                            <input type="number" name="cantidad" id="ajuste{{ $articulo->id }}" min="0" inputmode="numeric" value="{{ $articulo->cantidad }}" required>
                            <input type="text" name="nota" maxlength="255" placeholder="Nota (opcional)" aria-label="Nota del ajuste">
                            <button type="submit" class="boton-secundario">Ajustar</button>
                        </form>

                        @if ($articulo->movimientos->isNotEmpty())
                            <ol class="historial-articulo">
                                @foreach ($articulo->movimientos->take(10) as $movimiento)
                                    <li>
                                        <span class="tipo-movimiento tipo-{{ $movimiento->tipo }}">{{ ['entrada' => '+' . $movimiento->cantidad, 'salida' => '−' . $movimiento->cantidad, 'ajuste' => '= ' . $movimiento->cantidad][$movimiento->tipo] }}</span>
                                        <span>{{ $movimiento->created_at->format('d/m g:i a') }} · {{ $movimiento->usuario?->nombre ?? '—' }}{{ $movimiento->nota ? ' · ' . $movimiento->nota : '' }}</span>
                                        <span class="resultante">quedó {{ $movimiento->cantidad_resultante }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        @endif
                    </details>
                </article>
            @endforeach
        </div>
    @endif

<script src="{{ recurso('js/inventario.js') }}"></script>
@include('partials.footerPanel')
