@php
    use App\Models\ActividadBase;

    $esBase = $modo === 'base';
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ asset('css/desarrollo.css') }}">
<title>{{ $esBase ? 'Desarrollo base' : 'Editar desarrollo' }} · Juan XXIII</title>

@include('partials.header')

    <a class="enlace-volver" href="{{ $esBase ? route('desarrollo.editar', ['sexo' => $grupo]) : route('desarrollo.index', ['sexo' => $grupo]) }}">← {{ $esBase ? 'Volver al desarrollo del retiro' : 'Volver al desarrollo' }}</a>

    <div class="cabecera-vista">
        <h1 class="vista-titulo">{{ $esBase ? 'Desarrollo base' : 'Editar desarrollo del retiro' }}</h1>
        <div class="acciones-desarrollo">
            @if ($esBase)
                @if ($retiro)
                    <form method="POST" action="{{ route('desarrollo.copiarBase') }}" class="formulario-confirmar" data-confirmar="¿Copiar la base de {{ $grupo }} al {{ $retiro->nombre }}? Se reemplaza su desarrollo y se pierden los charlistas y los cambios hechos solo para ese retiro.">
                        @csrf
                        <input type="hidden" name="sexo" value="{{ $grupo }}">
                        <input type="hidden" name="reemplazar" value="1">
                        <button type="submit" class="boton-secundario">Copiar la base a este retiro</button>
                    </form>
                @endif
            @else
                <a class="boton-secundario" href="{{ route('desarrollo.base', ['sexo' => $grupo]) }}">Desarrollo base</a>
            @endif
        </div>
    </div>

    @if ($esBase)
        <p class="texto-ayuda texto-ayuda-arriba">Se usa para todos los retiros. Cambiarlo NO cambia un retiro que ya se preparó: para eso está «Copiar la base a este retiro».</p>
    @else
        <p class="texto-ayuda texto-ayuda-arriba">{{ $retiro->nombre }} · {{ $retiro->dias_texto }}. Los cambios son solo para este retiro. Aquí se escribe quién da cada charla.</p>
    @endif
    @include('partials.mensaje')

    @include('desarrollo.pestanas', ['ruta' => $esBase ? 'desarrollo.base' : 'desarrollo.editar'])

    @if (! $esBase && $filas->isEmpty())
        <p class="texto-vacio">El desarrollo de {{ $grupo }} todavía no está preparado para este retiro.</p>
        <form method="POST" action="{{ route('desarrollo.copiarBase') }}" class="formulario-preparar">
            @csrf
            <input type="hidden" name="sexo" value="{{ $grupo }}">
            <button type="submit" class="boton-amarillo">Preparar desde la base</button>
        </form>
    @else

        {{-- --------------------AGREGAR ACTIVIDAD------------------- --}}
        <details class="agregar-actividad" {{ $errors->any() && old('nombre') !== null ? 'open' : '' }}>
            <summary>Agregar actividad</summary>
            <form class="formulario-agregar" method="POST" action="{{ $esBase ? route('desarrollo.agregarBase') : route('desarrollo.agregar') }}">
                @csrf
                <input type="hidden" name="sexo" value="{{ $grupo }}">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="diaNueva">Día</label>
                    <select name="diaRelativo" id="diaNueva" required>
                        @foreach (ActividadBase::JORNADAS as $valor => $nombre)
                            <option value="{{ $valor }}" {{ (int) old('diaRelativo', 0) === $valor ? 'selected' : '' }}>{{ strtok($nombre, ' ') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="inicioNueva">Inicio</label>
                    <input type="time" name="horaInicio" id="inicioNueva" value="{{ old('horaInicio') }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="finNueva">Fin</label>
                    <input type="time" name="horaFin" id="finNueva" value="{{ old('horaFin') }}">
                </div>
                <div class="elemento-formulario elemento-nombre">
                    <label class="titulo-elemento" for="nombreNueva">Actividad</label>
                    <input type="text" name="nombre" id="nombreNueva" maxlength="100" value="{{ old('nombre') }}" required>
                </div>
                @unless ($esBase)
                    <div class="elemento-formulario elemento-nombre">
                        <label class="titulo-elemento" for="charlistaNueva">Charlista <small>(opcional)</small></label>
                        <input type="text" name="charlista" id="charlistaNueva" maxlength="100" value="{{ old('charlista') }}">
                    </div>
                @endunless
                <label class="casilla-charla">
                    <input type="checkbox" name="esCharla" value="1" {{ old('esCharla') ? 'checked' : '' }}>
                    Es charla
                </label>
                <button type="submit" class="boton-amarillo">Agregar</button>
            </form>
        </details>

        {{-- --------------------ACTIVIDADES------------------- --}}
        <form method="POST" action="{{ $esBase ? route('desarrollo.guardarBase') : route('desarrollo.guardar') }}" id="formulario-actividades">
            @csrf
            @method('PUT')

            @foreach ($filas->groupBy('dia') as $dia => $delDia)
                <h2 class="titulo-jornada">{{ ActividadBase::JORNADAS[$dia] ?? 'Día ' . ($dia + 1) }}</h2>
                <div class="lista-editor">
                    @foreach ($delDia as $fila)
                        @php
                            $campo = 'actividades[' . $fila['id'] . ']';
                        @endphp
                        <div class="fila-actividad {{ $fila['es_charla'] ? 'fila-charla' : '' }}">
                            <select name="{{ $campo }}[diaRelativo]" class="campo-dia" aria-label="Día">
                                @foreach (ActividadBase::JORNADAS as $valor => $nombre)
                                    <option value="{{ $valor }}" {{ $fila['dia'] === $valor ? 'selected' : '' }}>{{ strtok($nombre, ' ') }}</option>
                                @endforeach
                            </select>
                            <input type="time" name="{{ $campo }}[horaInicio]" value="{{ $fila['inicio'] }}" aria-label="Inicio" required>
                            <input type="time" name="{{ $campo }}[horaFin]" value="{{ $fila['fin'] }}" aria-label="Fin">
                            <input type="text" name="{{ $campo }}[nombre]" class="campo-nombre" maxlength="100" value="{{ $fila['nombre'] }}" aria-label="Actividad" required>
                            @unless ($esBase)
                                <input type="text" name="{{ $campo }}[charlista]" class="campo-charlista" maxlength="100" value="{{ $fila['charlista'] }}" placeholder="{{ $fila['es_charla'] ? 'Charlista' : '' }}" aria-label="Charlista">
                            @endunless
                            <label class="casilla-charla">
                                <input type="hidden" name="{{ $campo }}[esCharla]" value="0">
                                <input type="checkbox" name="{{ $campo }}[esCharla]" value="1" {{ $fila['es_charla'] ? 'checked' : '' }}>
                                Charla
                            </label>
                            <label class="casilla-quitar">
                                <input type="checkbox" name="{{ $campo }}[quitar]" value="1">
                                Quitar
                            </label>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <div class="barra-guardar" id="barra-guardar">
                <p class="barra-guardar-texto" id="texto-cambios">Sin cambios</p>
                <button type="submit" class="boton-amarillo">Guardar cambios</button>
            </div>
        </form>
    @endif

<script src="{{ asset('js/desarrollo.js') }}"></script>
@include('partials.footerPanel')
