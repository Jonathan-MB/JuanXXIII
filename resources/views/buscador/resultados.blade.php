@php
    use App\Support\Buscador;

    $ejemplos   = ['Carta', 'Directorio', 'Mesa 3', 'Camisetas', 'Nequi', 'Ofrenda', 'Santísimo', 'Llegada', 'Charlistas', 'Sobres'];
    $porGrupo   = 8; // los demás quedan en «Ver más»
@endphp

@if ($texto === '')
    <section class="ayuda-buscador">
        <p class="titulo-ayuda-buscador">Prueba con:</p>
        <div class="ejemplos-buscador">
            @foreach ($ejemplos as $ejemplo)
                <a class="ejemplo-buscador" href="{{ route('buscar', ['q' => $ejemplo]) }}">{{ $ejemplo }}</a>
            @endforeach
        </div>
        <p class="titulo-ayuda-buscador">Busca en:</p>
        <p class="donde-busca">{{ implode(' · ', Buscador::GRUPOS) }}.</p>
    </section>

@elseif ($total === 0)
    <section class="sin-resultados">
        <p class="sin-resultados-titulo">No encontré nada con «{{ $texto }}».</p>
        <ul>
            <li>Prueba con una sola palabra: «carta» en vez de «la carta del padre».</li>
            <li>Prueba con otra palabra parecida: «lista» o «hoja».</li>
            <li>Si es una persona que hizo el retiro antes, búscala en <a href="{{ route('historial.index', ['busqueda' => $texto]) }}">Historial</a>.</li>
        </ul>
    </section>

@else
    <p class="resumen-buscador">
        <strong>{{ $total }} {{ $total === 1 ? 'resultado' : 'resultados' }}</strong> para «{{ $texto }}»
        @if ($parecidos)
            <span class="aviso-parecidos">No encontré todas las palabras juntas; esto es lo más parecido.</span>
        @endif
    </p>

    @if ($grupos->count() > 1)
        <nav class="saltos-buscador" aria-label="Ir a un grupo">
            @foreach ($grupos as $grupo => $lista)
                <a href="#grupo-{{ $grupo }}">{{ Buscador::GRUPOS[$grupo] }} <span>{{ $lista->count() }}</span></a>
            @endforeach
        </nav>
    @endif

    @foreach ($grupos as $grupo => $lista)
        <section class="grupo-buscador" id="grupo-{{ $grupo }}">
            <h2 class="titulo-grupo-buscador">{{ Buscador::GRUPOS[$grupo] }} <span>{{ $lista->count() }}</span></h2>

            <ul class="lista-buscador">
                @foreach ($lista->take($porGrupo) as $resultado)
                    @include('buscador.tarjeta')
                @endforeach
            </ul>

            @if ($lista->count() > $porGrupo)
                <details class="mas-buscador">
                    <summary>Ver {{ $lista->count() - $porGrupo }} más</summary>
                    <ul class="lista-buscador">
                        @foreach ($lista->slice($porGrupo) as $resultado)
                            @include('buscador.tarjeta')
                        @endforeach
                    </ul>
                </details>
            @endif
        </section>
    @endforeach
@endif
