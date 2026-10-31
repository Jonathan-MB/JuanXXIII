@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/inventario.css') }}">
<title>Inventario · Juan XXIII</title>

@include('partials.header')

    <div class="cabecera-vista">
        <h1 class="vista-titulo">Inventario</h1>
        @unless ($sinEquipo)
            <a class="boton-secundario" href="{{ route('inventario.imprimirTodos') }}" target="_blank" rel="noopener">Imprimir todos</a>
        @endunless
    </div>
    @include('partials.mensaje')

    @if ($sinEquipo)
        <p class="texto-vacio">Tu cuenta no tiene un equipo asignado. Pídele a administración que te asigne uno para ver su inventario.</p>
    @else
        <p class="texto-ayuda texto-ayuda-arriba">Elige el equipo para ver su inventario.</p>
        <div class="rejilla-equipos">
            @foreach ($equipos as $equipo)
                <a class="tarjeta-equipo" href="{{ route('inventario.show', $equipo) }}">
                    <span class="nombre-equipo">{{ $equipo->nombre }}</span>
                    <span class="detalle-equipo">{{ $equipo->articulos_count }} {{ $equipo->articulos_count === 1 ? 'artículo' : 'artículos' }}</span>
                    @if ((int) $equipo->articulos_sum_por_comprar > 0)
                        <span class="chip chip-amarillo">Hay cosas por comprar</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

@include('partials.footerPanel')
