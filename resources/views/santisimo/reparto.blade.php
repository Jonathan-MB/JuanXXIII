@php
    use App\Models\SantisimoRegla;

    $dias = ['' => 'Todos los días', 0 => 'Viernes', 1 => 'Sábado', 2 => 'Domingo'];
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/santisimo.css') }}">
<title>Reparto del Santísimo · Juan XXIII</title>

@include('partials.header')

    <a class="enlace-volver" href="{{ route('santisimo.editar') }}">← Volver al horario del retiro</a>
    <h1 class="vista-titulo">Reparto automático del Santísimo</h1>
    <p class="texto-ayuda texto-ayuda-arriba">Los equipos con más personas reciben más franjas. Primero se ponen las horas fijas, nunca se pone a un equipo en una hora en la que no puede ir y las franjas de TODOS no se tocan. Después se puede corregir a mano.</p>
    @include('partials.mensaje')

    {{-- --------------------EQUIPOS------------------- --}}
    <section class="bloque-reparto">
        <h2 class="titulo-bloque-reparto">1. Tamaño de cada equipo</h2>
        <p class="texto-ayuda">Un número aproximado basta. Si no se sabe, déjalo en 0: cuenta como un equipo de una persona. Quien no participa solo cubre sus horas fijas.</p>
        <form method="POST" action="{{ route('santisimo.reparto.equipos') }}">
            @csrf
            @method('PUT')
            <div class="lista-equipos-reparto">
                @foreach ($equipos as $equipo)
                    <div class="fila-equipo-reparto">
                        <span class="nombre-equipo-reparto">{{ $equipo->nombre }}</span>
                        <label class="campo-personas">
                            <input type="number" name="equipos[{{ $equipo->id }}][personas]" min="0" max="500" value="{{ old('equipos.' . $equipo->id . '.personas', $equipo->cantidad_personas) }}" required>
                            personas
                        </label>
                        <label class="casilla-participa">
                            <input type="checkbox" name="equipos[{{ $equipo->id }}][participa]" value="1" {{ old('equipos.' . $equipo->id . '.participa', $equipo->participa_santisimo) ? 'checked' : '' }}>
                            Participa
                        </label>
                    </div>
                @endforeach
            </div>
            <button type="submit" class="boton-amarillo">Guardar equipos</button>
        </form>
    </section>

    {{-- --------------------REGLAS------------------- --}}
    <section class="bloque-reparto">
        <h2 class="titulo-bloque-reparto">2. Horas fijas y horas en que no pueden ir</h2>
        <p class="texto-ayuda">La hora final no se incluye: de 12:00 a. m. a 4:00 a. m. llega hasta la franja de las 3:30. Para llegar a la medianoche, escribe 11:59 p. m.</p>

        <form class="formulario-agregar-franja" method="POST" action="{{ route('santisimo.reparto.store') }}">
            @csrf
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="equipoRegla">Equipo</label>
                <select name="equipo" id="equipoRegla" required>
                    <option value="">Elige</option>
                    @foreach ($equipos as $equipo)
                        <option value="{{ $equipo->id }}" {{ (string) old('equipo') === (string) $equipo->id ? 'selected' : '' }}>{{ $equipo->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="tipoRegla">Regla</label>
                <select name="tipo" id="tipoRegla" required>
                    <option value="{{ SantisimoRegla::PROHIBIDA }}" {{ old('tipo') === SantisimoRegla::PROHIBIDA ? 'selected' : '' }}>No puede ir</option>
                    <option value="{{ SantisimoRegla::FIJA }}" {{ old('tipo') === SantisimoRegla::FIJA ? 'selected' : '' }}>Hora fija</option>
                </select>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="diaRegla">Día</label>
                <select name="diaRelativo" id="diaRegla">
                    @foreach ($dias as $valor => $nombre)
                        <option value="{{ $valor }}" {{ (string) old('diaRelativo', '') === (string) $valor ? 'selected' : '' }}>{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="desdeRegla">Desde</label>
                <input type="time" name="horaDesde" id="desdeRegla" value="{{ old('horaDesde') }}" required>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="hastaRegla">Hasta</label>
                <input type="time" name="horaHasta" id="hastaRegla" value="{{ old('horaHasta') }}" required>
            </div>
            <button type="submit" class="boton-amarillo">+ Agregar regla</button>
        </form>

        @if ($reglas->isEmpty())
            <p class="texto-vacio">Todavía no hay reglas: todos los equipos pueden ir a cualquier hora.</p>
        @else
            <div class="lista-franjas-editor">
                @foreach ($reglas as $regla)
                    <div class="fila-franja {{ $regla->tipo === SantisimoRegla::FIJA ? 'fila-todos' : '' }}">
                        <span class="chip {{ $regla->tipo === SantisimoRegla::FIJA ? 'chip-azul' : 'chip-rojo' }}">{{ $regla->tipo === SantisimoRegla::FIJA ? 'Hora fija' : 'No puede ir' }}</span>
                        <strong>{{ $regla->equipo->nombre }}</strong>
                        <span>{{ $regla->dia_texto }}, de {{ \Carbon\Carbon::parse($regla->hora_desde)->format('g:i a') }} a {{ \Carbon\Carbon::parse($regla->hora_hasta)->format('g:i a') }}</span>
                        <form method="POST" action="{{ route('santisimo.reparto.destroy', $regla) }}" class="formulario-confirmar" data-confirmar="¿Quitar esta regla de {{ $regla->equipo->nombre }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton-texto">Quitar</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- --------------------REPARTIR------------------- --}}
    <section class="bloque-reparto">
        <h2 class="titulo-bloque-reparto">3. Repartir</h2>
        <p class="texto-ayuda">Se borra lo que tenían las franjas (menos las de TODOS) y se vuelve a repartir. Lo que se haya puesto a mano se pierde.</p>
        <form class="formulario-agregar-franja formulario-confirmar" method="POST" action="{{ route('santisimo.reparto.repartir') }}" data-confirmar="¿Repartir el horario de nuevo? Se reemplaza lo que tenía cada franja, menos las de TODOS.">
            @csrf
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="destinoReparto">Qué horario</label>
                <select name="destino" id="destinoReparto" required>
                    @if ($retiro)
                        <option value="retiro">El de este retiro ({{ $retiro->nombre }})</option>
                    @endif
                    <option value="base">El horario base</option>
                </select>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="turnoReparto">Cada turno dura</label>
                <select name="turno" id="turnoReparto" required>
                    <option value="1">30 minutos</option>
                    <option value="2" selected>1 hora</option>
                    <option value="3">1 hora y media</option>
                    <option value="4">2 horas</option>
                </select>
            </div>
            <button type="submit" class="boton-amarillo">Repartir</button>
        </form>
    </section>

<script src="{{ recurso('js/santisimo.js') }}"></script>
@include('partials.footerPanel')
