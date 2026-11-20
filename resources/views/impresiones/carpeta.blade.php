@include('partials.headImpresion')

    @if ($faltantes)
        {{-- Solo en pantalla: al imprimir no sale --}}
        <p class="aviso-carpeta">Faltan {{ implode(' y ', array_unique($faltantes)) }}: el retiro todavía no tiene el desarrollo preparado.</p>
    @endif

    {{-- Primera hoja: el rótulo para pegar al frente de la carpeta (sin nombre, queda la línea para escribirlo) --}}
    <section class="pagina pagina-rotulo-carpeta">
        @foreach ($rotulos as $rotulo)
            <div class="rotulo-carpeta">
                <div class="rotulo-carpeta-cuerpo">
                    <img class="rotulo-carpeta-logo" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                    <p class="rotulo-carpeta-nombre">{!! $rotulo['nombre'] ? e($rotulo['nombre']) : '&nbsp;' !!}</p>
                    <p class="rotulo-carpeta-cargo">{{ $rotulo['cargo'] }}</p>
                </div>
                <p class="rotulo-carpeta-retiro">Retiro N° {{ $rotulo['numero'] }}</p>
            </div>
        @endforeach
    </section>

    {{-- Cada parte ya trae sus propias hojas; aquí solo van una tras otra --}}
    @foreach ($partes as $parte)
        {!! $parte !!}
    @endforeach

@include('partials.footerImpresion')
