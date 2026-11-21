@include('partials.headImpresion')

    {{--  Ramita de olivo en línea fina: se usa en las esquinas y junto a la
         cruz del membrete. Todo en tonos muy claros para gastar poca tinta --}}
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <defs>
            <symbol id="rama-olivo" viewBox="0 0 220 80">
                <path d="M6 72 C60 58 120 38 214 8" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                <g fill="none" stroke="currentColor" stroke-width="1.3">
                    <path d="M34 64 q10 -16 26 -18 q-8 14 -26 18z"/>
                    <path d="M40 66 q14 8 30 2 q-16 -8 -30 -2z"/>
                    <path d="M74 52 q10 -16 26 -18 q-8 14 -26 18z"/>
                    <path d="M82 54 q14 8 30 2 q-16 -8 -30 -2z"/>
                    <path d="M116 38 q10 -16 26 -18 q-8 14 -26 18z"/>
                    <path d="M124 40 q14 8 30 2 q-16 -8 -30 -2z"/>
                    <path d="M158 25 q10 -15 25 -17 q-8 13 -25 17z"/>
                    <path d="M166 27 q13 7 28 1 q-15 -7 -28 -1z"/>
                </g>
                <g fill="currentColor">
                    <circle cx="64" cy="62" r="3.2"/>
                    <circle cx="146" cy="36" r="3.2"/>
                </g>
            </symbol>
        </defs>
    </svg>

    @for ($hoja = 1; $hoja <= $cantidad; $hoja++)
        <section class="pagina hoja-carta {{ $renglones ? 'con-renglones' : '' }}">
            {{-- Marca de agua: el logo grande y muy suave en el centro --}}
            <img class="marca-agua-logo" src="{{ asset('img/logo.png') }}" alt="">

            {{-- Ramas finas en dos esquinas opuestas --}}
            <svg class="rama-esquina rama-arriba" aria-hidden="true"><use href="#rama-olivo"/></svg>
            <svg class="rama-esquina rama-abajo" aria-hidden="true"><use href="#rama-olivo"/></svg>

            <header class="membrete-carta">
                <img class="logo-carta" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                <p class="movimiento-carta">Movimiento de Retiros Parroquiales Juan XXIII</p>
                <p class="arquidiocesis-carta">Arquidiócesis de Cali</p>
                <div class="adorno-carta" aria-hidden="true">
                    <span class="linea-adorno"></span>
                    <svg class="rama-adorno rama-izquierda"><use href="#rama-olivo"/></svg>
                    <span class="cruz-adorno">✝</span>
                    <svg class="rama-adorno"><use href="#rama-olivo"/></svg>
                    <span class="linea-adorno"></span>
                </div>
            </header>

            <div class="cuerpo-carta"></div>

            <footer class="pie-carta">«Con Cristo todo y sin Cristo nada»</footer>
        </section>
    @endfor

@include('partials.footerImpresion')
