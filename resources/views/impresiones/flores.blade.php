@include('partials.headImpresion')

    {{-- Una hoja de tarjetas en blanco: se imprimen las copias que hagan falta --}}
    <section class="pagina pagina-flores pagina-{{ $papel }}">
        @for ($i = 0; $i < $tarjetas; $i++)
            <article class="tarjeta-flor">
                <img class="flor-logo" src="{{ asset('img/logo.png') }}" alt="">
                <div class="flor-campos">
                    <p class="flor-linea"><span>De:</span><span class="flor-espacio"></span></p>
                    <p class="flor-linea"><span>Para:</span><span class="flor-espacio"></span></p>
                    <p class="flor-linea flor-puesto">
                        <span>R:</span><span class="flor-espacio"></span>
                        <span>M:</span><span class="flor-espacio"></span>
                        <span>S:</span><span class="flor-espacio"></span>
                    </p>
                </div>
            </article>
        @endfor
    </section>

@include('partials.footerImpresion')
