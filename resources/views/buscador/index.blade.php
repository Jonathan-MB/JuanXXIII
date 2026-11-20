@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/buscador.css') }}">
<title>Buscar · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">Buscar</h1>
    <p class="texto-ayuda texto-ayuda-arriba">Escribe lo que buscas: una hoja, un letrero, un archivo, una persona, una mesa, una charla o algo del inventario. No importan las tildes ni si una letra quedó mal.</p>

    <form class="formulario-buscador" method="GET" action="{{ route('buscar') }}" role="search" id="formulario-buscador">
        <input class="caja-buscador" type="search" name="q" value="{{ $texto }}" id="caja-buscador"
               placeholder="Ej.: carta, mesa 5, nequi, camisetas, María…" aria-label="¿Qué buscas?" autocomplete="off" autofocus>
        <button type="submit" class="boton-azul">Buscar</button>
    </form>

    <div id="resultados-buscador" aria-live="polite">
        @include('buscador.resultados')
    </div>

<script src="{{ recurso('js/buscador.js') }}"></script>
@include('partials.footerPanel')
