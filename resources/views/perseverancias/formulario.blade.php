@php
    $editando = $perseverancia->exists;
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/perseverancias.css') }}">
<title>{{ $editando ? 'Editar perseverancia' : 'Nueva perseverancia' }} · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">{{ $editando ? 'Editar ' . $perseverancia->titulo : 'Nueva perseverancia' }}</h1>
    @include('partials.mensaje')

    <form class="formulario-perseverancia" method="POST" action="{{ $editando ? route('perseverancias.update', $perseverancia) : route('perseverancias.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($editando)
            @method('PUT')
        @endif

        {{-- --------------------DÓNDE------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Dónde</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="tipo">Tipo</label>
                    <input type="text" name="tipo" id="tipo" maxlength="40" list="tipos" value="{{ old('tipo', $perseverancia->tipo) }}" required>
                    <datalist id="tipos">
                        <option value="Perseverancia"></option>
                        <option value="Cuarto día"></option>
                        <option value="Escuela diocesana"></option>
                    </datalist>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="sector">Sector o barrio <small>(el nombre grande, ej.: El Rodeo)</small></label>
                    <input type="text" name="sector" id="sector" maxlength="60" value="{{ old('sector', $perseverancia->sector) }}">
                </div>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="parroquia">Parroquia o lugar</label>
                <input type="text" name="parroquia" id="parroquia" maxlength="120" value="{{ old('parroquia', $perseverancia->parroquia) }}" placeholder="Parroquia La Natividad del Señor" required>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" maxlength="120" value="{{ old('direccion', $perseverancia->direccion) }}" placeholder="Cra 25A # 39-36, B/ El Rodeo">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="mapa">Búsqueda en el mapa <small>(opcional: si el mapa no la encuentra bien con la dirección)</small></label>
                <input type="text" name="mapa" id="mapa" maxlength="160" value="{{ old('mapa', $perseverancia->mapa) }}" placeholder="{{ $perseverancia->busqueda_mapa }}">
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="coordenadas">Punto en el mapa <small>(en Google Maps: clic derecho sobre la parroquia y copia los números que salen arriba)</small></label>
                <input type="text" name="coordenadas" id="coordenadas" maxlength="40" inputmode="decimal" value="{{ old('coordenadas', $perseverancia->coordenadas) }}" placeholder="3.4372, -76.5225">
            </div>
        </fieldset>

        {{-- --------------------CUÁNDO------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Cuándo</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="dia">Día</label>
                    <input type="text" name="dia" id="dia" maxlength="30" list="dias" value="{{ old('dia', $perseverancia->dia) }}" required>
                    <datalist id="dias">
                        @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $dia)
                            <option value="{{ $dia }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="hora">Hora <small>(la del directorio)</small></label>
                    <input type="text" name="hora" id="hora" maxlength="20" value="{{ old('hora', $perseverancia->hora) }}" placeholder="7:00 p. m." required>
                </div>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="actividades">Lo que se hace ese día <small>(una por renglón, sale en el inicio)</small></label>
                <textarea name="actividades" id="actividades" rows="4" maxlength="500" placeholder="6:30 p. m. Rosario&#10;7:00 p. m. Sagrada Eucaristía&#10;8:00 p. m. Perseverancia">{{ old('actividades', $perseverancia->actividades) }}</textarea>
            </div>
        </fieldset>

        {{-- --------------------EN EL INICIO------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>En el inicio y el directorio</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="orden">Orden <small>(1 sale primero)</small></label>
                    <input type="number" name="orden" id="orden" min="1" max="999" value="{{ old('orden', $perseverancia->orden) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="imagen">Foto de fondo <small>(de la parroquia; PNG, JPG o WEBP, máximo 4 MB)</small></label>
                    <input type="file" name="imagen" id="imagen" accept="image/png,image/jpeg,image/webp">
                </div>
            </div>
            <div class="elemento-formulario">
                <label class="titulo-elemento" for="imagenCredito">Crédito de la foto <small>(si la foto es de internet y pide nombrar al autor)</small></label>
                <input type="text" name="imagenCredito" id="imagenCredito" maxlength="200" value="{{ old('imagenCredito', $perseverancia->imagen_credito) }}" placeholder="Foto: nombre del autor · licencia">
            </div>
            @if ($perseverancia->imagen)
                <div class="foto-actual">
                    <img src="{{ route('perseverancias.imagen', $perseverancia) }}?v={{ $perseverancia->updated_at?->timestamp }}" alt="Foto actual de {{ $perseverancia->titulo }}">
                    <label class="casilla-perseverancia">
                        <input type="checkbox" name="quitarImagen" value="1">
                        Quitar la foto
                    </label>
                </div>
            @endif
            <label class="casilla-perseverancia">
                <input type="hidden" name="enInicio" value="0">
                <input type="checkbox" name="enInicio" value="1" {{ old('enInicio', $perseverancia->en_inicio) ? 'checked' : '' }}>
                Sale en el inicio (carrusel y mapa)
            </label>
        </fieldset>

        <div class="acciones-formulario">
            <a class="boton-secundario" href="{{ route('perseverancias.index') }}">Cancelar</a>
            <button type="submit" class="boton-azul">{{ $editando ? 'Guardar cambios' : 'Agregar perseverancia' }}</button>
        </div>
    </form>

@include('partials.footerPanel')
