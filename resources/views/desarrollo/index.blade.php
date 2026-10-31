@php
    use App\Models\ActividadBase;
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/desarrollo.css') }}">
<title>Desarrollo del retiro · Juan XXIII</title>

@include('partials.header')

    <div class="cabecera-vista">
        <h1 class="vista-titulo">Desarrollo del retiro</h1>
        @if ($retiro)
            <div class="acciones-desarrollo">
                @if ($actividades->isNotEmpty())
                    <a class="boton-secundario" href="{{ route('desarrollo.imprimir', ['sexo' => $grupo]) }}" target="_blank">Imprimir {{ $grupo }}</a>
                    <a class="boton-secundario" href="{{ route('desarrollo.imprimir') }}" target="_blank">Imprimir ambos</a>
                @endif
                <a class="boton-azul" href="{{ route('desarrollo.editar', ['sexo' => $grupo]) }}">Editar</a>
            </div>
        @endif
    </div>
    @include('partials.mensaje')

    @if (! $retiro)
        <p class="texto-vacio">No hay un retiro próximo.</p>
    @else
        <p class="texto-ayuda texto-ayuda-arriba">{{ $retiro->nombre }} · {{ $retiro->dias_texto }}. Actividades y charlas para el papeleo de Secretaría; el de mujeres va más temprano.</p>

        @include('desarrollo.pestanas', ['ruta' => 'desarrollo.index'])

        @if ($actividades->isEmpty())
            <p class="texto-vacio">El desarrollo de {{ $grupo }} todavía no está preparado para este retiro.</p>
            <form method="POST" action="{{ route('desarrollo.copiarBase') }}" class="formulario-preparar">
                @csrf
                <input type="hidden" name="sexo" value="{{ $grupo }}">
                <button type="submit" class="boton-amarillo">Preparar desde la base</button>
            </form>
        @else
            @foreach ($jornadas as $jornada => $delDia)
                <section class="jornada">
                    <h2 class="titulo-jornada">{{ ActividadBase::JORNADAS[$jornada] ?? 'Día ' . ($jornada + 1) }}</h2>
                    <ul class="lista-actividades">
                        @foreach ($delDia as $actividad)
                            @php
                                $esAhora = $ahora && $ahora->id === $actividad->id;
                            @endphp
                            <li class="actividad {{ $actividad->es_charla ? 'actividad-charla' : '' }} {{ $esAhora ? 'actividad-ahora' : '' }} {{ $actividad->fin && $actividad->fin->lt(now()) ? 'actividad-pasada' : '' }}">
                                <span class="actividad-hora">{{ $actividad->inicio->format('g:i a') }}{{ $actividad->fin ? ' – ' . $actividad->fin->format('g:i a') : '' }}</span>
                                <span class="actividad-nombre">
                                    {{ $actividad->nombre }}
                                    @if ($actividad->es_charla)
                                        <small class="actividad-charlista">{{ $actividad->charlista ?: 'Charlista sin asignar' }}</small>
                                    @endif
                                </span>
                                @if ($actividad->duracion)
                                    <span class="actividad-duracion">{{ $actividad->duracion }} min</span>
                                @endif
                                @if ($esAhora)
                                    <span class="chip chip-amarillo">Ahora</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        @endif
    @endif

@include('partials.footerPanel')
