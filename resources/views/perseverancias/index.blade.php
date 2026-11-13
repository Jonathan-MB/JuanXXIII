@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/perseverancias.css') }}">
<title>Perseverancias · Juan XXIII</title>

@include('partials.header')

    <div class="cabecera-vista">
        <h1 class="vista-titulo">Perseverancias</h1>
        <a class="boton-azul" href="{{ route('perseverancias.create') }}">+ Nueva perseverancia</a>
    </div>
    @include('partials.mensaje')
    <p class="texto-ayuda texto-ayuda-arriba">Salen al pie del directorio en este orden, y en el inicio (carrusel y mapa) las que tienen «Sale en el inicio». Cuando cambie un día o un horario, cámbialo aquí.</p>

    @if ($perseverancias->isEmpty())
        <p class="texto-vacio">Todavía no hay perseverancias. Agrega la primera con <strong>+ Nueva perseverancia</strong>.</p>
    @else
        <div class="rejilla-perseverancias">
            @foreach ($perseverancias as $perseverancia)
                <article class="tarjeta-perseverancia">
                    {{-- Sin foto, un fondo de color para que se distingan --}}
                    <div class="foto-perseverancia fondo-{{ $loop->index % 6 }}" @if ($perseverancia->imagen) style="background-image: url('{{ route('perseverancias.imagen', $perseverancia) }}?v={{ $perseverancia->updated_at?->timestamp }}')" @endif>
                        <span class="orden-perseverancia">{{ $perseverancia->orden }}</span>
                        @unless ($perseverancia->en_inicio)
                            <span class="chip chip-gris">No sale en el inicio</span>
                        @endunless
                    </div>
                    <div class="datos-perseverancia">
                        <p class="tipo-perseverancia">{{ $perseverancia->tipo }}</p>
                        <h2 class="titulo-perseverancia">{{ $perseverancia->titulo }}</h2>
                        <p class="dia-perseverancia">{{ $perseverancia->dia }} · {{ $perseverancia->hora }}</p>
                        @if ($perseverancia->sector)
                            <p>{{ $perseverancia->parroquia }}</p>
                        @endif
                        @if ($perseverancia->direccion)
                            <p class="direccion-perseverancia">{{ $perseverancia->direccion }}</p>
                        @endif
                        @if ($perseverancia->lista_actividades)
                            <ul class="actividades-perseverancia">
                                @foreach ($perseverancia->lista_actividades as $actividad)
                                    <li>{{ $actividad }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="acciones-perseverancia">
                        @if ($perseverancia->direccion)
                            <a class="enlace-accion" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($perseverancia->busqueda_mapa) }}" target="_blank" rel="noopener">Ver en el mapa</a>
                        @endif
                        <a class="boton-secundario" href="{{ route('perseverancias.edit', $perseverancia) }}">Editar</a>
                        <form method="POST" action="{{ route('perseverancias.destroy', $perseverancia) }}" class="formulario-confirmar" data-confirmar="¿Quitar la perseverancia {{ $perseverancia->titulo }}? Deja de salir en el directorio y en el inicio.">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton-quitar">Quitar</button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

<script src="{{ recurso('js/perseverancias.js') }}"></script>
@include('partials.footerPanel')
