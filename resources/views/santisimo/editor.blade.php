@php
    $esBase = $modo === 'base';
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/santisimo.css') }}">
<title>{{ $esBase ? 'Horario base del Santísimo' : 'Editar Santísimo' }} · Juan XXIII</title>

@include('partials.header')

    <a class="enlace-volver" href="{{ $esBase ? route('santisimo.editar') : route('santisimo.index') }}">← {{ $esBase ? 'Volver al horario del retiro' : 'Volver al horario' }}</a>

    <div class="cabecera-vista">
        <h1 class="vista-titulo">{{ $esBase ? 'Horario base del Santísimo' : 'Santísimo del retiro' }}</h1>
        <div class="acciones-santisimo">
            @if ($esBase)
                @if ($retiro)
                    <form method="POST" action="{{ route('santisimo.copiarBase') }}" class="formulario-confirmar" data-confirmar="¿Copiar la base al {{ $retiro->nombre }}? Se reemplaza su horario y se pierden los cambios hechos solo para ese retiro.">
                        @csrf
                        <input type="hidden" name="reemplazar" value="1">
                        <button type="submit" class="boton-secundario">Copiar la base a este retiro</button>
                    </form>
                @endif
            @else
                @if ($filas->isNotEmpty())
                    <a class="boton-secundario" href="{{ route('santisimo.imprimir') }}" target="_blank">Imprimir</a>
                @endif
                <a class="boton-secundario" href="{{ route('santisimo.base') }}">Horario base</a>
            @endif
        </div>
    </div>

    @if ($esBase)
        <p class="texto-ayuda texto-ayuda-arriba">Se usa para todos los retiros. Cambiarlo NO cambia el horario de un retiro que ya se preparó: para eso está el botón «Copiar la base a este retiro».</p>
    @else
        <p class="texto-ayuda texto-ayuda-arriba">{{ $retiro->nombre }} · {{ $retiro->dias_texto }}. Los cambios aquí son solo para este retiro; la base no se toca.</p>
    @endif
    @include('partials.mensaje')

    @if (! $esBase && $filas->isEmpty())
        <p class="texto-vacio">Este retiro todavía no tiene horario.</p>
        <form method="POST" action="{{ route('santisimo.copiarBase') }}" class="formulario-preparar">
            @csrf
            <button type="submit" class="boton-amarillo">Preparar el horario desde la base</button>
        </form>
    @else

        {{-- --------------------RESUMEN------------------- --}}
        <section class="resumen-santisimo">
            <p class="resumen-linea">
                <span class="chip chip-azul">{{ $resumen['total'] }} franjas</span>
                <span class="chip chip-gris">{{ $resumen['todos'] }} de todos</span>
                @if ($resumen['vacias'])
                    <a class="chip chip-rojo" href="#" id="ir-vacia">{{ $resumen['vacias'] }} sin nadie</a>
                @else
                    <span class="chip chip-verde">Ninguna queda sola</span>
                @endif
            </p>
            <p class="resumen-linea resumen-equipos">
                @foreach ($resumen['porEquipo'] as $equipo)
                    <span class="chip chip-gris">{{ $equipo['nombre'] }}: {{ $equipo['franjas'] }}</span>
                @endforeach
            </p>
        </section>

        {{-- --------------------AGREGAR FRANJA (solo base)------------------- --}}
        @if ($esBase)
            <form class="formulario-agregar-franja" method="POST" action="{{ route('santisimo.agregarBase') }}">
                @csrf
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="diaFranja">Día</label>
                    <select name="diaRelativo" id="diaFranja" required>
                        @foreach ([0 => 'Viernes', 1 => 'Sábado', 2 => 'Domingo'] as $valor => $nombre)
                            <option value="{{ $valor }}" {{ (int) old('diaRelativo', 0) === $valor ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="horaFranja">Empieza a las</label>
                    <input type="time" name="horaInicio" id="horaFranja" step="1800" value="{{ old('horaInicio') }}" required>
                </div>
                <button type="submit" class="boton-amarillo">+ Agregar franja</button>
            </form>
        @endif

        {{-- --------------------FRANJAS------------------- --}}
        <form method="POST" action="{{ $esBase ? route('santisimo.guardarBase') : route('santisimo.guardar') }}" id="formulario-franjas">
            @csrf
            @method('PUT')

            @foreach ($filas->groupBy('dia') as $dia => $delDia)
                <h2 class="titulo-dia-editor">{{ $dia }}</h2>
                <div class="lista-franjas-editor">
                    @foreach ($delDia as $fila)
                        @php
                            $franja = $fila['modelo'];
                            $cubre  = $franja->todos ? 'todos' : (string) $franja->equipo_id;
                        @endphp
                        <div class="fila-franja {{ $franja->estaVacia() ? 'fila-vacia' : '' }} {{ $franja->todos ? 'fila-todos' : '' }}">
                            <span class="fila-hora">{{ $fila['hora']->format('g:i a') }} – {{ $fila['hora']->copy()->addMinutes(30)->format('g:i a') }}</span>
                            <select name="franjas[{{ $franja->id }}][cubre]" class="campo-cubre" aria-label="Quién cubre la franja de las {{ $fila['hora']->format('g:i a') }}">
                                <option value="" {{ $cubre === '' ? 'selected' : '' }}>— Sin nadie —</option>
                                <option value="todos" {{ $cubre === 'todos' ? 'selected' : '' }}>TODOS</option>
                                @foreach ($equipos as $equipo)
                                    <option value="{{ $equipo->id }}" {{ $cubre === (string) $equipo->id ? 'selected' : '' }}>{{ $equipo->nombre }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="franjas[{{ $franja->id }}][actividad]" class="campo-actividad" maxlength="60" value="{{ $franja->actividad }}" placeholder="Adoración" aria-label="Actividad">
                            @if ($esBase)
                                <button type="submit" form="quitar-{{ $franja->id }}" class="boton-texto">Quitar</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach

            <div class="barra-guardar" id="barra-guardar">
                <p class="barra-guardar-texto" id="texto-cambios">Sin cambios</p>
                <button type="submit" class="boton-amarillo">Guardar cambios</button>
            </div>
        </form>

        {{-- Fuera del formulario grande: un formulario no puede ir dentro de otro --}}
        @if ($esBase)
            @foreach ($filas as $fila)
                <form method="POST" action="{{ route('santisimo.quitarBase', $fila['modelo']) }}" id="quitar-{{ $fila['modelo']->id }}" class="formulario-confirmar" data-confirmar="¿Quitar la franja del {{ $fila['dia'] }} a las {{ $fila['hora']->format('g:i a') }} del horario base?">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif
    @endif

<script src="{{ recurso('js/santisimo.js') }}"></script>
@include('partials.footerPanel')
