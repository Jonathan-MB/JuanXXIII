@php
    use App\Models\Archivo;

    $nombreCategoria = Archivo::CATEGORIAS[$categoria];
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ asset('css/archivos.css') }}">
<title>{{ $nombreCategoria }} · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">{{ $nombreCategoria }}</h1>

    <nav class="pestanas-archivos" aria-label="Categorías">
        @foreach (Archivo::CATEGORIAS as $clave => $nombre)
            <a class="pestana {{ $clave === $categoria ? 'pestana-activa' : '' }}" href="{{ route('archivos.index', ['categoria' => $clave]) }}">{{ $nombre }} <span>{{ $conteos[$clave] ?? 0 }}</span></a>
        @endforeach
    </nav>

    @include('partials.mensaje')

    @if ($categoria === 'charlas')
        <p class="aviso-charlas">Las charlas son propiedad intelectual del Movimiento Juan XXIII: no se publican ni se comparten fuera del equipo del retiro.</p>
    @endif

    {{-- --------------------SUBIR------------------- --}}
    <details class="subir-archivos" {{ $errors->has('archivos') || $errors->has('archivos.*') ? 'open' : '' }}>
        <summary class="boton-amarillo">+ Subir archivos</summary>
        <form class="formulario-subir" method="POST" action="{{ route('archivos.store') }}" enctype="multipart/form-data" id="formulario-subir">
            @csrf
            <input type="hidden" name="categoria" value="{{ $categoria }}">
            <label class="zona-archivos" for="archivosNuevos">
                <span class="zona-titulo">Elige uno o varios archivos</span>
                <span class="zona-detalle">PDF, Word, Excel, PowerPoint o imágenes · hasta 20 archivos de 20 MB cada uno</span>
                <input type="file" name="archivos[]" id="archivosNuevos" multiple accept=".{{ implode(',.', Archivo::EXTENSIONES) }}" required>
            </label>
            <ul class="lista-elegidos" id="lista-elegidos"></ul>
            <p class="texto-ayuda">El título sale del nombre del archivo. Si empieza con un número ("002 - Quién eres"), ese número se usa para ordenar.</p>
            <button type="submit" class="boton-azul" id="boton-subir">Subir a {{ $nombreCategoria }}</button>
        </form>
    </details>

    {{-- --------------------LISTA------------------- --}}
    @if ($archivos->isEmpty())
        <p class="texto-vacio">Todavía no hay archivos en {{ $nombreCategoria }}. Súbelos con <strong>+ Subir archivos</strong>.</p>
    @else
        <input class="buscador buscador-archivos" type="search" id="buscar-archivo" placeholder="Buscar en {{ mb_strtolower($nombreCategoria) }}…" aria-label="Buscar archivo">
        <p class="texto-vacio" id="archivos-sin-resultados" hidden>Ningún archivo coincide con la búsqueda.</p>

        <ul class="lista-archivos">
            @foreach ($archivos as $archivo)
                <li class="fila-archivo" data-buscar="{{ mb_strtolower($archivo->numero . ' ' . $archivo->titulo . ' ' . $archivo->descripcion . ' ' . $archivo->nombre_original) }}">
                    <span class="tipo-archivo tipo-{{ $archivo->extension }}">{{ $archivo->tipo_texto }}</span>

                    <div class="datos-archivo">
                        <p class="titulo-archivo">
                            @if ($archivo->numero !== null)
                                <span class="numero-archivo">{{ str_pad($archivo->numero, 2, '0', STR_PAD_LEFT) }}</span>
                            @endif
                            {{ $archivo->titulo }}
                        </p>
                        <p class="detalle-archivo">{{ $archivo->tamano_texto }}{{ $archivo->descripcion ? ' · ' . $archivo->descripcion : '' }}</p>
                    </div>

                    <div class="acciones-archivo">
                        @if ($archivo->seVeEnNavegador())
                            <a class="boton-azul" href="{{ route('archivos.show', $archivo) }}" target="_blank" rel="noopener">Ver e imprimir</a>
                        @endif
                        <a class="boton-secundario" href="{{ route('archivos.show', ['archivo' => $archivo, 'descargar' => 1]) }}">Descargar</a>
                    </div>

                    <details class="editar-archivo">
                        <summary>Editar</summary>
                        <form class="formulario-editar-archivo" method="POST" action="{{ route('archivos.update', $archivo) }}">
                            @csrf
                            @method('PUT')
                            <input type="number" name="numero" min="0" max="999" value="{{ $archivo->numero }}" aria-label="Número" placeholder="N.º">
                            <input type="text" name="titulo" maxlength="150" value="{{ $archivo->titulo }}" aria-label="Título" required>
                            <input type="text" name="descripcion" maxlength="255" value="{{ $archivo->descripcion }}" aria-label="Descripción" placeholder="Descripción (opcional)">
                            <select name="categoria" aria-label="Categoría">
                                @foreach (Archivo::CATEGORIAS as $clave => $nombre)
                                    <option value="{{ $clave }}" {{ $clave === $archivo->categoria ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="boton-secundario">Guardar</button>
                        </form>
                        <form method="POST" action="{{ route('archivos.destroy', $archivo) }}" class="formulario-confirmar" data-confirmar="¿Quitar «{{ $archivo->titulo }}»? Se borra el archivo.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton-texto">Quitar archivo</button>
                        </form>
                    </details>
                </li>
            @endforeach
        </ul>
    @endif

<script src="{{ asset('js/archivos.js') }}"></script>
@include('partials.footerPanel')
