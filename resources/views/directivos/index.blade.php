@include('partials.head')

{{-- Misma presentación que la lista de Inscritos --}}
<link rel="stylesheet" href="{{ recurso('css/inscritos.css') }}">
<title>Inscritos del retiro · Directivos · Juan XXIII</title>

@include('partials.header')

    @if (! $retiro)
        <h1 class="vista-titulo">Inscritos del retiro</h1>
        <p class="texto-vacio">No hay un retiro próximo.</p>
    @else
        <h1 class="vista-titulo">Inscritos del retiro · {{ $retiro->nombre }}</h1>
        <p class="texto-ayuda">Solo para consultar. {{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}.</p>

        {{-- --------------------PESTAÑAS------------------- --}}
        <nav class="pestanas-inscritos" aria-label="Grupos">
            <a class="pestana {{ $grupo === 'hombres' ? 'pestana-activa' : '' }}" href="{{ route('directivos.index', ['sexo' => 'hombres']) }}">Hombres · Retiro {{ $retiro->numero_hombres }} <span>{{ (int) $conteos->hombres }}</span></a>
            <a class="pestana {{ $grupo === 'mujeres' ? 'pestana-activa' : '' }}" href="{{ route('directivos.index', ['sexo' => 'mujeres']) }}">Mujeres · Retiro {{ $retiro->numero_mujeres }} <span>{{ (int) $conteos->mujeres }}</span></a>
        </nav>

        {{-- --------------------BUSCAR------------------- --}}
        <form class="filtros-inscritos" method="GET" action="{{ route('directivos.index') }}">
            <input type="hidden" name="sexo" value="{{ $grupo }}">
            <input class="buscador" type="search" name="busqueda" value="{{ $busqueda }}" placeholder="Buscar por nombre…" aria-label="Buscar por nombre">
            <button type="submit" class="boton-azul">Buscar</button>
            @if ($busqueda !== '')
                <a class="enlace-accion" href="{{ route('directivos.index', ['sexo' => $grupo]) }}">Quitar filtro</a>
            @endif
        </form>

        @if ($inscritos->isEmpty())
            <p class="texto-vacio">No hay inscritos {{ $busqueda !== '' ? 'con ese nombre' : 'en este grupo todavía' }}.</p>
        @else
            <div class="contenedor-tabla">
                <table class="tabla-general">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Ciudad / barrio</th>
                            <th>Celular</th>
                            <th>Edad</th>
                            <th>Puesto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inscritos as $inscrito)
                            <tr>
                                <td>{{ $inscrito->persona->apellidos }}, {{ $inscrito->persona->nombres }}</td>
                                <td>{{ $inscrito->persona->ciudad }} / {{ $inscrito->persona->barrio }}</td>
                                <td>{{ $inscrito->persona->celular }}</td>
                                <td>{{ (int) $inscrito->persona->fecha_nacimiento->diffInYears($retiro->fecha_inicio) }}</td>
                                <td>{{ $inscrito->mesa ? 'Mesa ' . $inscrito->mesa->numero . ' · Silla ' . $inscrito->silla : '—' }}</td>
                                <td><span class="chip {{ $inscrito->estado === 'confirmado' ? 'chip-azul' : 'chip-gris' }}">{{ $inscrito->nombre_estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="texto-ayuda">{{ $inscritos->count() }} {{ $inscritos->count() === 1 ? 'persona' : 'personas' }} en la lista.</p>
        @endif
    @endif

@include('partials.footerPanel')
