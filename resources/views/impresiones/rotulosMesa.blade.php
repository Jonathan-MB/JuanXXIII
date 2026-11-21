@include('partials.headImpresion')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Cinzel+Decorative:wght@700&display=swap">

    {{--  Por cada mesa dos tiras de 30 × 4,4 cm (santo y número) para pegar
         en la madera. Dos mesas por hoja; se corta por las líneas punteadas.
         No llevan el número del retiro: sirven para todos --}}
    @forelse ($grupos as $sexo => $mesas)
        @foreach ($mesas->chunk(2) as $hoja)
            <section class="pagina pagina-rotulos">
                @foreach ($hoja as $mesa)
                    {{-- Cara 1: el santo --}}
                    <div class="tira-mesa tira-santo">
                        <div class="marco-tira">
                            @if ($mesa['imagen'])
                                <img class="imagen-santo" src="{{ $mesa['imagen'] }}" alt="{{ $mesa['nombre'] }}">
                            @else
                                <span class="imagen-santo sin-imagen">✝</span>
                            @endif
                            <div class="texto-tira">
                                @php $textoSanto = $mesa['nombre'] ?: 'Mesa ' . $mesa['numero']; @endphp
                                {{-- Los nombres largos («Santa Laura Montoya») se achican para caber --}}
                                <p class="nombre-santo" style="font-size: {{ min(40, round(680 / max(1, mb_strlen($textoSanto)), 1)) }}pt">{{ $textoSanto }}</p>
                                <p class="adorno-tira"><span></span>✦<span></span></p>
                                <p class="movimiento-tira">Movimiento de Retiros Parroquiales Juan XXIII</p>
                            </div>
                            <img class="logo-tira" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                        </div>
                    </div>

                    {{-- Cara 2: el número de la mesa --}}
                    <div class="tira-mesa tira-numero">
                        <div class="marco-tira">
                            <img class="logo-tira" src="{{ asset('img/logo.png') }}" alt="">
                            <div class="texto-tira">
                                <p class="numero-mesa-tira">Mesa <span>N.º {{ $mesa['numero'] }}</span></p>
                                @if ($mesa['nombre'])
                                    <p class="santo-pequeno">{{ $mesa['nombre'] }}</p>
                                @endif
                            </div>
                            <img class="logo-tira" src="{{ asset('img/logo.png') }}" alt="">
                        </div>
                    </div>
                @endforeach
            </section>
        @endforeach
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay mesas todavía. Créalas en Mesas o escribe sus nombres en Mesas › Nombres de mesas.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
