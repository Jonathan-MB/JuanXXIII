<li class="tarjeta-buscador {{ $resultado['url'] ? '' : 'tarjeta-buscador-ajena' }}">
    <div class="texto-tarjeta-buscador">
        @if ($resultado['url'])
            <a class="titulo-tarjeta-buscador" href="{{ $resultado['url'] }}" @if ($resultado['pestanaNueva']) target="_blank" rel="noopener" @endif>{{ $resultado['titulo'] }}</a>
        @else
            <p class="titulo-tarjeta-buscador">{{ $resultado['titulo'] }}</p>
        @endif
        @if ($resultado['cuando'])
            <p class="cuando-buscador">{{ $resultado['cuando'] }}</p>
        @endif
        @if ($resultado['detalle'])
            <p class="detalle-buscador">{{ $resultado['detalle'] }}</p>
        @endif
    </div>

    @if ($resultado['url'])
        <a class="boton-secundario boton-abrir-buscador" href="{{ $resultado['url'] }}" @if ($resultado['pestanaNueva']) target="_blank" rel="noopener" @endif>Abrir</a>
    @elseif ($resultado['nota'])
        <p class="nota-buscador">{{ $resultado['nota'] }}</p>
    @endif
</li>
