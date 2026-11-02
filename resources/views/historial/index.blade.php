@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/historial.css') }}">
<title>Historial · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">Historial de retiristas</h1>
    <p class="texto-ayuda texto-ayuda-arriba">Busca si alguien ya hizo el retiro y en qué número. En la aplicación hay {{ $totales['retiristas'] }} retiristas de {{ $totales['personas'] }} personas en total, y {{ $totales['anteriores'] }} retiristas de antes de la aplicación.</p>
    @include('partials.mensaje')

    <form class="buscar-historial" method="GET" action="{{ route('historial.index') }}">
        <input class="buscador" type="search" name="busqueda" value="{{ $busqueda }}" placeholder="Nombre, apellido o cédula…" aria-label="Nombre, apellido o cédula" minlength="3" autofocus required>
        <button type="submit" class="boton-azul">Buscar</button>
    </form>

    {{-- --------------------IMPORTAR------------------- --}}
    <details class="importar-historial" {{ $errors->any() ? 'open' : '' }}>
        <summary>Importar retiristas de antes de la aplicación (Excel o CSV)</summary>
        <p class="texto-ayuda">El archivo necesita una fila de títulos con <strong>Cédula</strong> (o Documento) y <strong>Nombre</strong> (o Nombres y Apellidos). Si trae <strong>Sexo</strong>, <strong>Retiro</strong> (el número), <strong>Año</strong> o <strong>Celular</strong>, también se toman. Si el archivo es de un solo retiro y no trae esas columnas, escríbelas abajo. Se puede volver a cargar: no se duplica nadie.</p>
        <p class="texto-ayuda"><strong>Ojo:</strong> carga solo a quienes terminaron el retiro (por ejemplo, la hoja de mesa y silla «M-S»), no las respuestas del formulario: quien queda aquí ya no se puede volver a inscribir.</p>
        <form class="formulario-importar" method="POST" action="{{ route('historial.importar') }}" enctype="multipart/form-data">
            @csrf
            <div class="elemento-formulario elemento-archivo">
                <label class="titulo-elemento" for="archivoHistorial">Archivo</label>
                <input type="file" name="archivo" id="archivoHistorial" accept=".xlsx,.csv" required>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="hojaHistorial">Hoja <small>(vacío = la primera)</small></label>
                <input type="text" name="hoja" id="hojaHistorial" maxlength="60" placeholder="M-S MUJERES" value="{{ old('hoja') }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="sexoHistorial">Sexo <small>(si no viene)</small></label>
                <select name="sexo" id="sexoHistorial">
                    <option value="">—</option>
                    <option value="Masculino" {{ old('sexo') === 'Masculino' ? 'selected' : '' }}>Hombres</option>
                    <option value="Femenino" {{ old('sexo') === 'Femenino' ? 'selected' : '' }}>Mujeres</option>
                </select>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="numeroHistorial">Retiro N.º <small>(si no viene)</small></label>
                <input type="number" name="numeroRetiro" id="numeroHistorial" min="1" max="999" value="{{ old('numeroRetiro') }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="anioHistorial">Año <small>(si no viene)</small></label>
                <input type="number" name="anio" id="anioHistorial" min="1960" max="2100" value="{{ old('anio') }}">
            </div>
            <button type="submit" class="boton-amarillo">Importar</button>
        </form>
    </details>

    @if ($busqueda !== '' && mb_strlen($busqueda) < 3)
        <p class="texto-vacio">Escribe al menos 3 letras o números.</p>
    @elseif ($busqueda !== '' && $personas->isEmpty() && $anteriores->isEmpty())
        <p class="texto-vacio">Nadie coincide con "{{ $busqueda }}". Si hizo el retiro antes de usar esta aplicación, puede que todavía no esté en el historial.</p>
    @endif

    @if ($personas->isNotEmpty())
        <p class="texto-ayuda">{{ $personas->count() }} {{ $personas->count() === 1 ? 'persona encontrada' : 'personas encontradas' }}{{ $personas->count() === 50 ? ' (se muestran las primeras 50; escribe más para afinar)' : '' }}.</p>

        <ul class="resultados-historial">
            @foreach ($personas as $persona)
                @php
                    $terminado = $persona->inscripciones->firstWhere('estado', \App\Models\Inscripcion::TERMINO);
                @endphp
                <li class="tarjeta-historial {{ $terminado ? 'ya-vivio' : '' }}">
                    <div class="cabecera-historial">
                        <div>
                            <p class="nombre-historial">{{ $persona->nombre_completo }}</p>
                            <p class="detalle-historial">CC {{ $persona->cedula }} · {{ $persona->ciudad }} / {{ $persona->barrio }} · {{ $persona->celular }}</p>
                        </div>
                        @if ($terminado)
                            <p class="sello-historial">
                                Retiro N.º {{ $terminado->retiro->numeroPara($persona->sexo) }}
                                <span>{{ $persona->sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }} · {{ $terminado->retiro->fecha_inicio->locale('es')->translatedFormat('F \d\e Y') }}</span>
                            </p>
                        @else
                            <span class="chip chip-gris">No ha terminado un retiro</span>
                        @endif
                    </div>

                    <ol class="inscripciones-historial">
                        @foreach ($persona->inscripciones as $inscripcion)
                            <li>
                                <span class="chip {{ $inscripcion->estado === 'termino' ? 'chip-verde' : ($inscripcion->estaFuera() ? 'chip-rojo' : 'chip-azul') }}">{{ $inscripcion->nombre_estado }}</span>
                                Retiro {{ $inscripcion->retiro->numeroPara($persona->sexo) }} · {{ $inscripcion->retiro->dias_texto }} de {{ $inscripcion->retiro->fecha_inicio->year }}
                                @if ($inscripcion->mesa_id)
                                    · Mesa {{ $inscripcion->mesa?->numero }}, silla {{ $inscripcion->silla }}
                                @endif
                                <a class="enlace-accion" href="{{ route('inscritos.show', $inscripcion) }}">Ver ficha</a>
                            </li>
                        @endforeach
                    </ol>
                </li>
            @endforeach
        </ul>
    @endif

    {{-- --------------------DE ANTES DE LA APLICACIÓN------------------- --}}
    @if ($anteriores->isNotEmpty())
        <h2 class="titulo-anteriores">De antes de la aplicación</h2>
        <ul class="resultados-historial">
            @foreach ($anteriores as $anterior)
                <li class="tarjeta-historial ya-vivio">
                    <div class="cabecera-historial">
                        <div>
                            <p class="nombre-historial">{{ $anterior->nombre }}</p>
                            <p class="detalle-historial">CC {{ $anterior->cedula }}{{ $anterior->celular ? ' · ' . $anterior->celular : '' }}</p>
                        </div>
                        <p class="sello-historial">
                            {{ $anterior->numero_retiro ? 'Retiro N.º ' . $anterior->numero_retiro : 'Ya vivió el retiro' }}
                            <span>{{ $anterior->sexo ? ($anterior->sexo === 'Femenino' ? 'Mujeres' : 'Hombres') : 'Sin sexo' }}{{ $anterior->anio ? ' · ' . $anterior->anio : '' }}</span>
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

@include('partials.footerPanel')
