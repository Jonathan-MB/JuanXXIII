@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $mesas)
        @php
            $color       = $sexo === 'Femenino' ? 'letrero-mujeres' : 'letrero-hombres';
            $retiroTexto = 'Retiro N.º ' . $retiro->numeroPara($sexo) . ' · ' . ($sexo === 'Femenino' ? 'Mujeres' : 'Hombres');

            // Un lugar ("Directores") o una hoja por mesa ("Mesa 1")
            $letreros = $lugar
                ? collect([['principal' => $lugar, 'nombre' => null]])
                : $mesas->map(fn ($mesa) => ['principal' => 'Mesa ' . $mesa->numero, 'nombre' => $mesa->nombre]);
        @endphp

        @foreach ($letreros as $letrero)
            {{-- Hoja carta horizontal, para pegar en la pared --}}
            <section class="pagina pagina-letrero {{ $color }}">
                <div class="letrero-marco">
                    <img class="letrero-logo" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                    <div class="letrero-texto">
                        <p class="letrero-principal {{ mb_strlen($letrero['principal']) > 9 ? 'letrero-largo' : '' }}">{{ $letrero['principal'] }}</p>
                        @if ($letrero['nombre'])
                            <p class="letrero-nombre">{{ $letrero['nombre'] }}</p>
                        @endif
                        <p class="letrero-retiro">{{ $retiroTexto }}</p>
                    </div>
                </div>
            </section>
        @endforeach
    @endforeach

@include('partials.footerImpresion')
