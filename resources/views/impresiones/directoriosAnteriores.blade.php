@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/impresiones.css') }}">
<title>Directorios anteriores · Juan XXIII</title>

@include('partials.header')

    {{--  Aparte de Impresiones a propósito: aquí solo están los retiros que ya
         terminaron, para no confundirlos con el que se está haciendo --}}
    <h1 class="vista-titulo">Directorios de retiros anteriores</h1>

    @if ($anteriores->isEmpty())
        <p class="texto-vacio">Todavía no hay retiros terminados.</p>
    @else
        <p class="texto-ayuda">El directorio de cada retiro que ya terminó, con sus mesas, sillas y rectorado. Se abre en una pestaña nueva: para descargarlo, <strong>Imprimir</strong> y elegir <strong>Guardar como PDF</strong>. Las perseverancias, el Cuarto día y el WhatsApp salen como están hoy.</p>
        <div class="rejilla-impresiones">
            @foreach ($anteriores as $anterior)
                <article class="tarjeta-impresion">
                    <h2 class="nombre-impresion">Retiro {{ $anterior->numero_hombres }} hombres · {{ $anterior->numero_mujeres }} mujeres</h2>
                    <p class="cuando-impresion">{{ $anterior->dias_texto }} de {{ $anterior->fecha_inicio->year }}@if ($anterior->lugar) · {{ $anterior->lugar }}@endif</p>
                    <div class="botones-impresion">
                        <a class="boton-azul" href="{{ route('impresiones.directorio', ['retiro' => $anterior->id, 'sexo' => 'hombres']) }}" target="_blank" rel="noopener">Hombres</a>
                        <a class="boton-azul" href="{{ route('impresiones.directorio', ['retiro' => $anterior->id, 'sexo' => 'mujeres']) }}" target="_blank" rel="noopener">Mujeres</a>
                        <a class="boton-azul" href="{{ route('impresiones.directorio', ['retiro' => $anterior->id]) }}" target="_blank" rel="noopener">Los dos</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

@include('partials.footerPanel')
