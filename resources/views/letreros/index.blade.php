@include('partials.head')

{{-- Las tarjetas y el formulario de letreros de mesa son los mismos de Impresiones --}}
<link rel="stylesheet" href="{{ recurso('css/impresiones.css') }}">
<link rel="stylesheet" href="{{ recurso('css/letreros.css') }}">
<title>Letreros · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">Letreros</h1>
    <p class="texto-ayuda texto-ayuda-arriba">Cada letrero se ve aquí tal como sale impreso. Ábrelo para verlo grande e imprimirlo en hoja carta horizontal.</p>

    @unless ($retiro)
        <p class="texto-ayuda">No hay un retiro próximo: «Ayudas de Dios» y «Nequi» salen sin el número del retiro ni la cuenta.</p>
    @endunless

    <div class="rejilla-impresiones">
        @foreach ($letreros as $clave => [$nombre, $descripcion])
            <article class="tarjeta-impresion">
                {{-- Vista previa: la misma hoja en pequeño, sin descargar nada --}}
                <a class="miniatura-letrero" href="{{ route('letreros.show', $clave) }}" target="_blank" rel="noopener" aria-label="Ver {{ $nombre }}">
                    <iframe src="{{ route('letreros.show', ['clave' => $clave, 'miniatura' => 1]) }}" loading="lazy" scrolling="no" tabindex="-1" title="Vista previa de {{ $nombre }}"></iframe>
                </a>
                <h2 class="nombre-impresion">{{ $nombre }}</h2>
                <p class="descripcion-impresion">{{ $descripcion }}</p>
                <a class="boton-azul" href="{{ route('letreros.show', $clave) }}" target="_blank" rel="noopener">Ver e imprimir</a>
            </article>
        @endforeach

        @include('impresiones.formularioLetreros')
    </div>

    <p class="texto-ayuda">Al empezar el retiro se imprimen todos desde aquí. Los letreros especiales que no estén en esta lista se suben en <a href="{{ route('archivos.index', ['categoria' => 'letreros']) }}">Archivos › Letreros</a>.</p>

<script src="{{ recurso('js/letreros.js') }}"></script>
@include('partials.footerPanel')
