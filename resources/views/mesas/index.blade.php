@php
    $grupo = $sexo === 'Femenino' ? 'mujeres' : 'hombres';
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/llegada.css') }}">
<title>Mesas · Juan XXIII</title>

@include('partials.header')

    @if (! $retiro)
        <h1 class="vista-titulo">Mesas</h1>
        <p class="texto-vacio">No hay un retiro próximo.</p>
    @else
        <div class="cabecera-vista">
            <h1 class="vista-titulo">Mesas · {{ $retiro->nombre }}</h1>
            <div class="acciones-cabecera">
                <a class="boton-secundario" href="{{ route('impresiones.catequistas', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Imprimir catequistas por mesa</a>
                <a class="boton-secundario" href="{{ route('impresiones.directorio', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Imprimir directorio</a>
                <a class="boton-secundario" href="{{ route('impresiones.ubicacion', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Imprimir hojas de ubicación</a>
                <a class="boton-secundario" href="{{ route('llegada.index', ['sexo' => $grupo]) }}">Ir a la llegada</a>
            </div>
        </div>
        @include('partials.mensaje')

        <nav class="pestanas-llegada" aria-label="Grupos">
            <a class="pestana {{ $grupo === 'hombres' ? 'pestana-activa' : '' }}" href="{{ route('mesas.index', ['sexo' => 'hombres']) }}">Hombres · Retiro {{ $retiro->numero_hombres }}</a>
            <a class="pestana {{ $grupo === 'mujeres' ? 'pestana-activa' : '' }}" href="{{ route('mesas.index', ['sexo' => 'mujeres']) }}">Mujeres · Retiro {{ $retiro->numero_mujeres }}</a>
        </nav>

        <p class="texto-ayuda texto-ayuda-mesas">Las mesas aparecen solas cuando, en <strong>Llegada</strong>, escribes la mesa y la silla de cada persona, o las agregas aquí. Aquí también les pones nombre y los catequistas: CT (titular) y CA (auxiliar).</p>

        {{-- --------------------AGREGAR MESA------------------- --}}
        <details class="agregar-mesa" {{ $errors->any() ? 'open' : '' }}>
            <summary class="boton-amarillo">+ Agregar mesa</summary>
            <form class="formulario-agregar-mesa" method="POST" action="{{ route('mesas.store') }}">
                @csrf
                <input type="hidden" name="sexo" value="{{ $sexo }}">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="numeroNueva">Mesa N.º</label>
                    <input type="number" name="numero" id="numeroNueva" min="1" max="99" value="{{ old('numero', ($mesas->max('numero') ?? 0) + 1) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="sillasNueva">Sillas</label>
                    <input type="number" name="cantidadSillas" id="sillasNueva" min="1" max="{{ \App\Models\Mesa::MAXIMO_SILLAS }}" value="{{ old('cantidadSillas', $mesas->last()?->cantidad_sillas ?? 8) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="nombreNueva">Nombre <small>(opcional)</small></label>
                    <input type="text" name="nombre" id="nombreNueva" maxlength="60" value="{{ old('nombre') }}">
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="ctNueva">CT</label>
                    <input type="text" name="catequistaTitular" id="ctNueva" maxlength="100" value="{{ old('catequistaTitular') }}">
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="caNueva">CA</label>
                    <input type="text" name="catequistaAuxiliar" id="caNueva" maxlength="100" value="{{ old('catequistaAuxiliar') }}">
                </div>
                <button type="submit" class="boton-azul">Agregar</button>
            </form>
        </details>

        @if ($mesas->isEmpty())
            <p class="texto-vacio">Todavía no hay mesas para {{ $grupo }}.</p>
        @else
            <div class="rejilla-config-mesas">
                @foreach ($mesas as $mesa)
                    <form class="tarjeta-config-mesa" method="POST" action="{{ route('mesas.update', $mesa) }}">
                        @csrf
                        @method('PUT')
                        <div class="cabecera-config-mesa">
                            <p class="numero-mesa">Mesa {{ $mesa->numero }}</p>
                            <span class="chip chip-gris">{{ $mesa->sentados_count }} sentados</span>
                        </div>

                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="nombre{{ $mesa->id }}">Nombre de la mesa <small>(opcional)</small></label>
                            <input type="text" name="nombre" id="nombre{{ $mesa->id }}" maxlength="60" value="{{ $mesa->nombre }}">
                        </div>
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="ct{{ $mesa->id }}">CT · Catequista titular</label>
                            <input type="text" name="catequistaTitular" id="ct{{ $mesa->id }}" maxlength="100" value="{{ $mesa->catequista_titular }}">
                            {{-- Para el directorio --}}
                            <div class="datos-catequista">
                                <input type="tel" inputmode="numeric" data-solo-numeros name="titularCelular" maxlength="20" value="{{ $mesa->titular_celular }}" placeholder="Celular" aria-label="Celular del CT de la mesa {{ $mesa->numero }}">
                                <input type="text" name="titularCiudad" maxlength="60" value="{{ $mesa->titular_ciudad }}" placeholder="Ciudad" aria-label="Ciudad del CT de la mesa {{ $mesa->numero }}">
                                <input type="text" name="titularBarrio" maxlength="60" value="{{ $mesa->titular_barrio }}" placeholder="Barrio" aria-label="Barrio del CT de la mesa {{ $mesa->numero }}">
                                <label class="cumple-catequista">Cumpleaños <input type="date" name="titularNacimiento" value="{{ $mesa->titular_nacimiento?->format('Y-m-d') }}" max="{{ now()->toDateString() }}" aria-label="Cumpleaños del CT de la mesa {{ $mesa->numero }}"></label>
                            </div>
                        </div>
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="ca{{ $mesa->id }}">CA · Catequista auxiliar</label>
                            <input type="text" name="catequistaAuxiliar" id="ca{{ $mesa->id }}" maxlength="100" value="{{ $mesa->catequista_auxiliar }}">
                            <div class="datos-catequista">
                                <input type="tel" inputmode="numeric" data-solo-numeros name="auxiliarCelular" maxlength="20" value="{{ $mesa->auxiliar_celular }}" placeholder="Celular" aria-label="Celular del CA de la mesa {{ $mesa->numero }}">
                                <input type="text" name="auxiliarCiudad" maxlength="60" value="{{ $mesa->auxiliar_ciudad }}" placeholder="Ciudad" aria-label="Ciudad del CA de la mesa {{ $mesa->numero }}">
                                <input type="text" name="auxiliarBarrio" maxlength="60" value="{{ $mesa->auxiliar_barrio }}" placeholder="Barrio" aria-label="Barrio del CA de la mesa {{ $mesa->numero }}">
                                <label class="cumple-catequista">Cumpleaños <input type="date" name="auxiliarNacimiento" value="{{ $mesa->auxiliar_nacimiento?->format('Y-m-d') }}" max="{{ now()->toDateString() }}" aria-label="Cumpleaños del CA de la mesa {{ $mesa->numero }}"></label>
                            </div>
                        </div>
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="sillas{{ $mesa->id }}">Sillas</label>
                            <input type="number" name="cantidadSillas" id="sillas{{ $mesa->id }}" min="1" max="{{ \App\Models\Mesa::MAXIMO_SILLAS }}" value="{{ $mesa->cantidad_sillas }}" required>
                        </div>

                        <div class="acciones-config-mesa">
                            <button type="submit" class="boton-azul">Guardar</button>
                            @if ($mesa->inscripciones_count === 0)
                                <button type="submit" class="boton-texto boton-quitar-mesa" form="quitar-mesa-{{ $mesa->id }}">Quitar mesa</button>
                            @endif
                        </div>
                    </form>
                @endforeach
            </div>

            {{-- Fuera de la rejilla: dentro, cada formulario vacío ocupaba una casilla y dejaba huecos --}}
            @foreach ($mesas->where('inscripciones_count', 0) as $mesa)
                <form id="quitar-mesa-{{ $mesa->id }}" method="POST" action="{{ route('mesas.destroy', $mesa) }}" class="formulario-confirmar" data-confirmar="¿Quitar la mesa {{ $mesa->numero }}? Está vacía.">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
        @endif
    @endif

<script src="{{ recurso('js/llegada.js') }}"></script>
@include('partials.footerPanel')
