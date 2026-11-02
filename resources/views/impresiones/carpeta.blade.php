@include('partials.headImpresion')

    @if ($faltantes)
        {{-- Solo en pantalla: al imprimir no sale --}}
        <p class="aviso-carpeta">Faltan {{ implode(' y ', array_unique($faltantes)) }}: el retiro todavía no tiene el desarrollo preparado.</p>
    @endif

    {{-- Cada parte ya trae sus propias hojas; aquí solo van una tras otra --}}
    @foreach ($partes as $parte)
        {!! $parte !!}
    @endforeach

@include('partials.footerImpresion')
