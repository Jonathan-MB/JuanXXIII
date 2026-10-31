@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/inscritos.css') }}">
<title>Inscritos · Juan XXIII</title>

@include('partials.header')

    @if (! $retiro)
        <h1 class="vista-titulo">Inscritos</h1>
        <p class="texto-vacio">No hay un retiro próximo. Créalo en <strong>Retiros</strong> para empezar a recibir inscripciones.</p>
    @else
        <div class="cabecera-vista">
            <h1 class="vista-titulo">Inscritos · {{ $retiro->nombre }}</h1>
            <div class="acciones-cabecera">
                <a class="boton-secundario" href="{{ route('exportar.inscritos') }}">Descargar para Excel</a>
                <a class="boton-secundario" href="{{ route('inscripcion') }}" target="_blank" rel="noopener">Abrir el formulario de inscripción</a>
            </div>
        </div>
        @include('partials.mensaje')

        {{-- --------------------PESTAÑAS------------------- --}}
        <nav class="pestanas-inscritos" aria-label="Grupos">
            <a class="pestana {{ $pestana === 'hombres' ? 'pestana-activa' : '' }}" href="{{ route('inscritos.index', ['pestana' => 'hombres']) }}">Hombres · Retiro {{ $retiro->numero_hombres }} <span>{{ $conteos['hombres'] }}</span></a>
            <a class="pestana {{ $pestana === 'mujeres' ? 'pestana-activa' : '' }}" href="{{ route('inscritos.index', ['pestana' => 'mujeres']) }}">Mujeres · Retiro {{ $retiro->numero_mujeres }} <span>{{ $conteos['mujeres'] }}</span></a>
            <a class="pestana {{ $pestana === 'fuera' ? 'pestana-activa' : '' }}" href="{{ route('inscritos.index', ['pestana' => 'fuera']) }}">No asistieron o abandonaron <span>{{ $conteos['fuera'] }}</span></a>
        </nav>

        {{-- --------------------FILTROS------------------- --}}
        <form class="filtros-inscritos" method="GET" action="{{ route('inscritos.index') }}">
            <input type="hidden" name="pestana" value="{{ $pestana }}">
            <input class="buscador" type="search" name="busqueda" value="{{ $busqueda }}" placeholder="Buscar por nombre o cédula…" aria-label="Buscar por nombre o cédula">
            <select name="pago" aria-label="Filtrar por pago">
                <option value="">Todos los pagos</option>
                <option value="completo" {{ $pago === 'completo' ? 'selected' : '' }}>Pagaron todo</option>
                <option value="abono" {{ $pago === 'abono' ? 'selected' : '' }}>Con abono</option>
                <option value="sin_pago" {{ $pago === 'sin_pago' ? 'selected' : '' }}>Sin pago</option>
            </select>
            <button type="submit" class="boton-azul">Buscar</button>
            @if ($busqueda !== '' || $pago)
                <a class="enlace-accion" href="{{ route('inscritos.index', ['pestana' => $pestana]) }}">Quitar filtros</a>
            @endif
        </form>

        @if ($inscritos->isEmpty())
            <p class="texto-vacio">No hay inscritos {{ $busqueda !== '' || $pago ? 'con esos filtros' : 'en este grupo todavía' }}.</p>
        @else
            <div class="contenedor-tabla">
                <table class="tabla-general">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Celular</th>
                            <th>Edad</th>
                            <th>Pago</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inscritos as $inscrito)
                            <tr>
                                <td><a class="enlace-inscrito" href="{{ route('inscritos.show', $inscrito) }}">{{ $inscrito->persona->apellidos }}, {{ $inscrito->persona->nombres }}</a></td>
                                <td>{{ $inscrito->persona->cedula }}</td>
                                <td>{{ $inscrito->persona->celular }}</td>
                                <td>{{ (int) $inscrito->persona->fecha_nacimiento->diffInYears($retiro->fecha_inicio) }}</td>
                                <td>@include('partials.chipPago', ['inscrito' => $inscrito])</td>
                                <td><span class="chip {{ $inscrito->estado === 'confirmado' ? 'chip-azul' : 'chip-gris' }}">{{ $inscrito->nombre_estado }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="texto-ayuda">{{ $inscritos->count() }} {{ $inscritos->count() === 1 ? 'persona' : 'personas' }} en la lista. Haz clic en un nombre para ver la ficha completa.</p>
        @endif
    @endif

@include('partials.footerPanel')
