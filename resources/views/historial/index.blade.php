@include('partials.head')

<link rel="stylesheet" href="{{ asset('css/historial.css') }}">
<title>Historial · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">Historial de retiristas</h1>
    <p class="texto-ayuda texto-ayuda-arriba">Busca si alguien ya hizo el retiro y en qué número. Hay {{ $totales['retiristas'] }} retiristas registrados de {{ $totales['personas'] }} personas en total.</p>

    <form class="buscar-historial" method="GET" action="{{ route('historial.index') }}">
        <input class="buscador" type="search" name="busqueda" value="{{ $busqueda }}" placeholder="Nombre, apellido o cédula…" aria-label="Nombre, apellido o cédula" minlength="3" autofocus required>
        <button type="submit" class="boton-azul">Buscar</button>
    </form>

    @if ($busqueda !== '' && mb_strlen($busqueda) < 3)
        <p class="texto-vacio">Escribe al menos 3 letras o números.</p>
    @elseif ($busqueda !== '' && $personas->isEmpty())
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

@include('partials.footerPanel')
