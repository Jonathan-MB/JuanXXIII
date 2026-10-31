@php
    use App\Models\TareaCronograma;

    // Desde 7 días antes hasta el domingo del retiro
    $dias = collect(range(-7, 2))->mapWithKeys(fn ($dia) => [$dia => TareaCronograma::nombreDia($dia)]);
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ asset('css/cronograma.css') }}">
<title>Lista de tareas · Juan XXIII</title>

@include('partials.header')

    <a class="enlace-volver" href="{{ route('panel') }}#cronograma">← Volver al inicio</a>
    <h1 class="vista-titulo">Lista de tareas del retiro</h1>
    <p class="texto-ayuda texto-ayuda-arriba">Es la misma para todos los retiros. En el inicio cada tarea se marca como hecha y se pone en rojo cuando falta 1 hora o menos sin marcarse.</p>
    @include('partials.mensaje')

    {{-- --------------------NUEVA TAREA------------------- --}}
    <form class="formulario-tarea tarea-nueva" method="POST" action="{{ route('cronograma.store') }}">
        @csrf
        <div class="elemento-formulario">
            <label class="titulo-elemento" for="diaNueva">Día</label>
            <select name="diaRelativo" id="diaNueva" required>
                @foreach ($dias as $valor => $nombre)
                    <option value="{{ $valor }}" {{ (int) old('diaRelativo', 0) === $valor ? 'selected' : '' }}>{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
        <div class="elemento-formulario">
            <label class="titulo-elemento" for="horaNueva">Hora</label>
            <input type="time" name="hora" id="horaNueva" value="{{ old('hora') }}" required>
        </div>
        <div class="elemento-formulario elemento-descripcion">
            <label class="titulo-elemento" for="descripcionNueva">Qué hay que hacer</label>
            <input type="text" name="descripcion" id="descripcionNueva" maxlength="200" value="{{ old('descripcion') }}" required>
        </div>
        <div class="elemento-formulario">
            <label class="titulo-elemento" for="responsableNueva">Responsable <small>(opcional)</small></label>
            <input type="text" name="responsable" id="responsableNueva" maxlength="60" list="equipos-responsables" value="{{ old('responsable') }}">
        </div>
        <button type="submit" class="boton-amarillo">+ Agregar tarea</button>
    </form>

    <datalist id="equipos-responsables">
        @foreach (\App\Models\Equipo::orderBy('nombre')->pluck('nombre') as $equipo)
            <option value="{{ $equipo }}"></option>
        @endforeach
        <option value="Rectorado"></option>
    </datalist>

    {{-- --------------------TAREAS------------------- --}}
    @if ($tareas->isEmpty())
        <p class="texto-vacio">Todavía no hay tareas. Agrega la primera con el formulario de arriba.</p>
    @else
        @foreach ($tareas->groupBy('dia_relativo') as $dia => $delDia)
            <h2 class="titulo-dia-editor">{{ TareaCronograma::nombreDia($dia) }}</h2>
            <div class="lista-tareas-editor">
                @foreach ($delDia as $tarea)
                    <div class="fila-tarea-editor">
                        <form class="formulario-tarea" method="POST" action="{{ route('cronograma.update', $tarea) }}">
                            @csrf
                            @method('PUT')
                            <select name="diaRelativo" aria-label="Día">
                                @foreach ($dias as $valor => $nombre)
                                    <option value="{{ $valor }}" {{ $tarea->dia_relativo === $valor ? 'selected' : '' }}>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <input type="time" name="hora" value="{{ substr($tarea->hora, 0, 5) }}" aria-label="Hora" required>
                            <input type="text" name="descripcion" class="campo-descripcion" maxlength="200" value="{{ $tarea->descripcion }}" aria-label="Qué hay que hacer" required>
                            <input type="text" name="responsable" maxlength="60" list="equipos-responsables" value="{{ $tarea->responsable }}" aria-label="Responsable" placeholder="Responsable">
                            <button type="submit" class="boton-secundario">Guardar</button>
                        </form>
                        <form method="POST" action="{{ route('cronograma.destroy', $tarea) }}" class="formulario-confirmar" data-confirmar="¿Quitar «{{ $tarea->descripcion }}» de la lista? Se quita para todos los retiros.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton-texto">Quitar</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif

<script src="{{ asset('js/cronograma.js') }}"></script>
@include('partials.footerPanel')
