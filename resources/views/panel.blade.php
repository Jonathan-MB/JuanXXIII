@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/panel.css') }}">
<title>Panel · Retiros Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">Hola, {{ auth()->user()->nombre }}</h1>
    @include('partials.mensaje')

    @if ($retiro)
        <section class="tarjeta-retiro-actual">
            <div class="retiro-actual-numeros">
                <p class="retiro-actual-etiqueta">Retiro actual</p>
                <p class="retiro-actual-nombre">{{ $retiro->numero_hombres }} Hombres · {{ $retiro->numero_mujeres }} Mujeres</p>
                <p class="retiro-actual-fechas">{{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }} · {{ $retiro->lugar }}</p>
            </div>

            <div class="retiro-actual-estado">
                @if ($retiro->inscripciones_abiertas)
                    <span class="chip chip-verde">Inscripciones abiertas</span>
                @else
                    <span class="chip chip-gris">Inscripciones cerradas</span>
                @endif
                <span class="chip chip-azul">Faltan {{ max(0, (int) now()->startOfDay()->diffInDays($retiro->fecha_inicio, false)) }} días</span>
            </div>
        </section>

        <section class="resumen-inscritos">
            <div class="cifra-inscritos">
                <p class="cifra-numero">{{ $inscritos['Masculino'] + $inscritos['Femenino'] }}</p>
                <p class="cifra-etiqueta">inscritos en total</p>
            </div>
            <div class="cifra-inscritos">
                <p class="cifra-numero">{{ $inscritos['Masculino'] }}</p>
                <p class="cifra-etiqueta">hombres · Retiro {{ $retiro->numero_hombres }}</p>
            </div>
            <div class="cifra-inscritos">
                <p class="cifra-numero">{{ $inscritos['Femenino'] }}</p>
                <p class="cifra-etiqueta">mujeres · Retiro {{ $retiro->numero_mujeres }}</p>
            </div>
        </section>
    @else
        <section class="tarjeta-retiro-actual tarjeta-sin-retiro">
            <div>
                <p class="retiro-actual-etiqueta">Retiro actual</p>
                <p class="retiro-actual-nombre">Todavía no hay un próximo retiro creado</p>
                @if (auth()->user()->tieneRol(\App\Models\Rol::ADMINISTRACION, \App\Models\Rol::SECRETARIA))
                    <a class="boton-azul" href="{{ route('retiros.create') }}">Crear el próximo retiro</a>
                @endif
            </div>
        </section>
    @endif

    @if ($retiro && auth()->user()->tieneRol(\App\Models\Rol::ADMINISTRACION, \App\Models\Rol::SECRETARIA, \App\Models\Rol::DIRECTIVOS))
        @php
            $total  = $cronograma->flatten()->count();
            $hechas = $cronograma->flatten()->where('estado', 'hecha')->count();
        @endphp

        {{-- --------------------CRONOGRAMA Y CHECKLIST------------------- --}}
        <section class="seccion-cronograma" id="cronograma">
            <div class="cabecera-cronograma">
                <div>
                    <h2 class="subtitulo-vista">Qué se debe hacer y a qué hora</h2>
                    <p class="texto-avance">Una tarea sin marcar se pone en rojo cuando falta 1 hora o menos, o si ya pasó su hora.</p>
                </div>
                @if (auth()->user()->tieneRol(\App\Models\Rol::ADMINISTRACION, \App\Models\Rol::SECRETARIA))
                    <a class="boton-secundario" href="{{ route('cronograma.index') }}">Editar la lista</a>
                @endif
            </div>

            @if ($total === 0)
                <p class="texto-vacio">La lista de tareas todavía está vacía. Agrégalas en <strong>Editar la lista</strong>; es la misma para todos los retiros.</p>
            @else
                <div class="avance-cronograma" aria-label="Avance">
                    <div class="barra-avance"><span style="width: {{ round($hechas * 100 / $total) }}%"></span></div>
                    <p><strong>{{ $hechas }}</strong> de {{ $total }} hechas</p>
                </div>

                @foreach ($cronograma as $dia => $filas)
                    <div class="dia-cronograma">
                        <h3 class="titulo-dia">{{ \App\Models\TareaCronograma::nombreDia($dia) }} · {{ $filas->first()->limite->locale('es')->translatedFormat('j \d\e F') }}</h3>
                        <ul class="lista-tareas">
                            @foreach ($filas as $fila)
                                <li class="tarea tarea-{{ $fila->estado }}" data-limite="{{ $fila->limite->toIso8601String() }}" data-hecha="{{ $fila->hecha ? 1 : 0 }}">
                                    <form method="POST" action="{{ route('cronograma.marcar', $fila->tarea) }}" class="formulario-marcar">
                                        @csrf
                                        <button type="submit" class="casilla-tarea" aria-label="{{ $fila->hecha ? 'Desmarcar' : 'Marcar como hecha' }}: {{ $fila->tarea->descripcion }}">{{ $fila->hecha ? '✓' : '' }}</button>
                                    </form>
                                    <span class="hora-tarea">{{ $fila->limite->format('g:i a') }}</span>
                                    <div class="texto-tarea">
                                        <p>{{ $fila->tarea->descripcion }}</p>
                                        <p class="detalle-tarea">
                                            {{ $fila->tarea->responsable ? 'Responsable: ' . $fila->tarea->responsable : '' }}
                                            @if ($fila->hecha)
                                                {{ $fila->tarea->responsable ? '·' : '' }} Hecha a las {{ $fila->hecha->hecha_en->format('g:i a') }} por {{ $fila->hecha->usuario?->nombre ?? '—' }}
                                            @endif
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        </section>
    @endif

<script src="{{ recurso('js/panel.js') }}"></script>
@include('partials.footerPanel')
