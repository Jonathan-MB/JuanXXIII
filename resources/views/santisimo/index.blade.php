@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/santisimo.css') }}">
<title>Santísimo · Juan XXIII</title>

@include('partials.header')

    <div class="cabecera-vista">
        <h1 class="vista-titulo">Horario del Santísimo</h1>
        @if ($puedeEditar && $retiro)
            <div class="acciones-santisimo">
                @if ($franjas->isNotEmpty())
                    <a class="boton-secundario" href="{{ route('santisimo.imprimir') }}" target="_blank">Imprimir</a>
                @endif
                <a class="boton-azul" href="{{ route('santisimo.editar') }}">Editar horario</a>
            </div>
        @endif
    </div>
    @include('partials.mensaje')

    @if (! $retiro)
        <p class="texto-vacio">No hay un retiro próximo.</p>
    @elseif ($franjas->isEmpty())
        <p class="texto-vacio">El horario del {{ $retiro->nombre }} todavía no está listo.</p>
        @if ($puedeEditar)
            <form method="POST" action="{{ route('santisimo.copiarBase') }}" class="formulario-preparar">
                @csrf
                <button type="submit" class="boton-amarillo">Preparar el horario desde la base</button>
            </form>
        @endif
    @else
        <p class="texto-ayuda texto-ayuda-arriba">{{ $retiro->nombre }} · {{ $retiro->dias_texto }}. Franjas de media hora: el Santísimo nunca queda solo.</p>

        {{-- --------------------MI EQUIPO------------------- --}}
        @if ($miEquipo)
            <section class="tarjeta-mi-equipo">
                <p class="mi-equipo-nombre">Tu equipo: <strong>{{ $miEquipo->nombre }}</strong></p>
                @if ($proximo)
                    <p class="mi-equipo-etiqueta">{{ now()->between($proximo->inicio, $proximo->fin) ? 'Turno en curso' : 'Próximo turno' }}</p>
                    <p class="mi-equipo-turno">{{ ucfirst($proximo->inicio->locale('es')->translatedFormat('l')) }} {{ $proximo->inicio->format('g:i a') }} – {{ $proximo->fin->format('g:i a') }}</p>
                @elseif ($misTurnos->isNotEmpty())
                    <p class="mi-equipo-etiqueta">Ya pasaron todos los turnos de tu equipo.</p>
                @else
                    <p class="mi-equipo-etiqueta">Tu equipo no tiene turnos en este horario.</p>
                @endif
                @if ($misTurnos->isNotEmpty())
                    <p class="mi-equipo-total">{{ $misTurnos->count() }} {{ $misTurnos->count() === 1 ? 'franja' : 'franjas' }} en total</p>
                @endif
            </section>
        @endif

        {{-- --------------------PESTAÑAS------------------- --}}
        @if ($miEquipo && $misTurnos->isNotEmpty())
            <div class="pestanas-santisimo" role="tablist">
                <button type="button" class="pestana-santisimo pestana-activa" data-vista="mias" role="tab">Mi equipo</button>
                <button type="button" class="pestana-santisimo" data-vista="todas" role="tab">Todo el horario</button>
            </div>
        @endif

        <div class="horario-santisimo {{ $miEquipo && $misTurnos->isNotEmpty() ? 'solo-mias' : '' }}" id="horario-santisimo">
            @foreach ($franjas->groupBy(fn ($franja) => $franja->inicio->toDateString()) as $delDia)
                <section class="dia-santisimo">
                    <h2 class="titulo-dia-santisimo">{{ ucfirst($delDia->first()->inicio->locale('es')->translatedFormat('l j \d\e F')) }}</h2>
                    <ul class="lista-franjas">
                        @foreach ($delDia as $franja)
                            @php
                                $esMia   = $miEquipo && $franja->equipo_id === $miEquipo->id;
                                $esAhora = $ahora && $ahora->id === $franja->id;
                                $clases  = collect([
                                    $esMia ? 'franja-mia' : '',
                                    $esAhora ? 'franja-ahora' : '',
                                    $franja->todos ? 'franja-todos' : '',
                                    $franja->estaVacia() ? 'franja-vacia' : '',
                                    $franja->fin->lt(now()) ? 'franja-pasada' : '',
                                ])->filter()->implode(' ');
                            @endphp
                            <li class="franja {{ $clases }}" {{ $esAhora ? 'id=franja-ahora' : '' }}>
                                <span class="franja-hora">{{ $franja->inicio->format('g:i a') }}</span>
                                <span class="franja-quien">
                                    @if ($franja->todos)
                                        Todos
                                    @elseif ($franja->equipo)
                                        {{ $franja->equipo->nombre }}
                                    @else
                                        Sin asignar
                                    @endif
                                </span>
                                <span class="franja-actividad">{{ $franja->actividad_texto }}</span>
                                @if ($esAhora)
                                    <span class="chip chip-amarillo">Ahora</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>
    @endif

<script src="{{ recurso('js/santisimo.js') }}"></script>
@include('partials.footerPanel')
