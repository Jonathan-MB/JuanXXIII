@php
    $persona   = $inscripcion->persona;
    $contactos = old('contactosEmergencia', $inscripcion->contactosEmergencia
        ->map(fn ($contacto) => [
            'nombre'       => $contacto->nombre,
            'parentesco'   => $contacto->parentesco,
            'celular'      => $contacto->celular,
            'telefonoFijo' => $contacto->telefono_fijo,
            'correo'       => $contacto->correo,
        ])
        ->pad(2, [])
        ->all());
    $tomaRemedios = old('tomaMedicamentos', $persona->medicamentos ? '1' : '0');
    $hayPadrino   = old('tienePadrino', $inscripcion->tiene_padrino ? '1' : '0');
    $formaPago    = old('formaPago', $inscripcion->forma_pago);
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/inscritos.css') }}">
<title>Editar · {{ $persona->nombre_completo }}</title>

@include('partials.header')

    <a class="enlace-volver" href="{{ route('inscritos.show', $inscripcion) }}">← Volver a la ficha</a>
    <h1 class="vista-titulo">Editar a {{ $persona->nombre_completo }}</h1>
    @include('partials.mensaje')

    <form class="formulario-inscrito" method="POST" action="{{ route('inscritos.update', $inscripcion) }}">
        @csrf
        @method('PUT')

        {{-- --------------------DATOS PERSONALES------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Datos personales</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="sexo">Sexo</label>
                    <select name="sexo" id="sexo" required>
                        <option value="Masculino" {{ old('sexo', $persona->sexo) === 'Masculino' ? 'selected' : '' }}>Hombre</option>
                        <option value="Femenino" {{ old('sexo', $persona->sexo) === 'Femenino' ? 'selected' : '' }}>Mujer</option>
                    </select>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="nombres">Nombres</label>
                    <input type="text" name="nombres" id="nombres" maxlength="60" value="{{ old('nombres', $persona->nombres) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="apellidos">Apellidos</label>
                    <input type="text" name="apellidos" id="apellidos" maxlength="60" value="{{ old('apellidos', $persona->apellidos) }}" required>
                </div>
            </div>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="cedula">Cédula</label>
                    <input type="text" name="cedula" id="cedula" maxlength="20" value="{{ old('cedula', $persona->cedula) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="fechaNacimiento">Fecha de nacimiento</label>
                    <input type="date" name="fechaNacimiento" id="fechaNacimiento" value="{{ old('fechaNacimiento', $persona->fecha_nacimiento->format('Y-m-d')) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="celular">Celular</label>
                    <input type="tel" name="celular" id="celular" maxlength="20" value="{{ old('celular', $persona->celular) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="correo">Correo</label>
                    <input type="email" name="correo" id="correo" maxlength="100" value="{{ old('correo', $persona->correo) }}" required>
                </div>
            </div>
            <div class="linea-formulario">
                <div class="elemento-formulario elemento-ancho">
                    <label class="titulo-elemento" for="direccion">Dirección</label>
                    <input type="text" name="direccion" id="direccion" maxlength="100" value="{{ old('direccion', $persona->direccion) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="barrio">Barrio</label>
                    <input type="text" name="barrio" id="barrio" maxlength="60" value="{{ old('barrio', $persona->barrio) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="ciudad">Ciudad</label>
                    <input type="text" name="ciudad" id="ciudad" maxlength="60" value="{{ old('ciudad', $persona->ciudad) }}" required>
                </div>
            </div>
        </fieldset>

        {{-- --------------------SALUD------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Salud y sacramentos</legend>
            <div class="casillas-sacramentos">
                <label class="casilla"><input type="checkbox" name="bautismo" value="1" {{ old('bautismo', $persona->bautismo) ? 'checked' : '' }}> Bautismo</label>
                <label class="casilla"><input type="checkbox" name="primeraComunion" value="1" {{ old('primeraComunion', $persona->primera_comunion) ? 'checked' : '' }}> Primera comunión</label>
                <label class="casilla"><input type="checkbox" name="confirmacion" value="1" {{ old('confirmacion', $persona->confirmacion) ? 'checked' : '' }}> Confirmación</label>
                <label class="casilla"><input type="checkbox" name="matrimonio" value="1" {{ old('matrimonio', $persona->matrimonio) ? 'checked' : '' }}> Matrimonio</label>
            </div>
            <div class="linea-formulario linea-separada">
                <div class="elemento-formulario">
                    <span class="titulo-elemento">¿Toma medicamentos?</span>
                    <div class="opciones-en-linea">
                        <label><input type="radio" name="tomaMedicamentos" value="0" {{ $tomaRemedios === '0' ? 'checked' : '' }}> No</label>
                        <label><input type="radio" name="tomaMedicamentos" value="1" {{ $tomaRemedios === '1' ? 'checked' : '' }}> Sí</label>
                    </div>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="eps">EPS</label>
                    <input type="text" name="eps" id="eps" maxlength="60" value="{{ old('eps', $persona->eps) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="tallaCamiseta">Talla</label>
                    <select name="tallaCamiseta" id="tallaCamiseta" required>
                        @foreach (\App\Models\Persona::TALLAS as $talla)
                            <option value="{{ $talla }}" {{ old('tallaCamiseta', $persona->talla_camiseta) === $talla ? 'selected' : '' }}>{{ $talla }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="elemento-formulario linea-separada" id="bloque-medicamentos">
                <label class="titulo-elemento" for="medicamentos">Medicamentos y horario</label>
                <input type="text" name="medicamentos" id="medicamentos" maxlength="255" value="{{ old('medicamentos', $persona->medicamentos) }}">
            </div>
        </fieldset>

        {{-- --------------------CONTACTOS------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Contactos de emergencia</legend>
            @foreach ([0, 1] as $i)
                <div class="linea-formulario {{ $i ? 'linea-separada' : '' }}">
                    <div class="elemento-formulario">
                        <label class="titulo-elemento" for="contacto{{ $i }}Nombre">Contacto {{ $i + 1 }}</label>
                        <input type="text" name="contactosEmergencia[{{ $i }}][nombre]" id="contacto{{ $i }}Nombre" maxlength="100" value="{{ $contactos[$i]['nombre'] ?? '' }}" required>
                    </div>
                    <div class="elemento-formulario">
                        <label class="titulo-elemento" for="contacto{{ $i }}Parentesco">Parentesco</label>
                        <input type="text" name="contactosEmergencia[{{ $i }}][parentesco]" id="contacto{{ $i }}Parentesco" maxlength="45" value="{{ $contactos[$i]['parentesco'] ?? '' }}" required>
                    </div>
                    <div class="elemento-formulario">
                        <label class="titulo-elemento" for="contacto{{ $i }}Celular">Celular</label>
                        <input type="tel" name="contactosEmergencia[{{ $i }}][celular]" id="contacto{{ $i }}Celular" maxlength="20" value="{{ $contactos[$i]['celular'] ?? '' }}" required>
                    </div>
                    <div class="elemento-formulario">
                        <label class="titulo-elemento" for="contacto{{ $i }}Fijo">Fijo</label>
                        <input type="tel" name="contactosEmergencia[{{ $i }}][telefonoFijo]" id="contacto{{ $i }}Fijo" maxlength="20" value="{{ $contactos[$i]['telefonoFijo'] ?? '' }}">
                    </div>
                    <div class="elemento-formulario">
                        <label class="titulo-elemento" for="contacto{{ $i }}Correo">Correo</label>
                        <input type="email" name="contactosEmergencia[{{ $i }}][correo]" id="contacto{{ $i }}Correo" maxlength="100" value="{{ $contactos[$i]['correo'] ?? '' }}">
                    </div>
                </div>
            @endforeach
        </fieldset>

        {{-- --------------------PAGO Y PADRINO------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Forma de pago y padrino</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario elemento-ancho">
                    <label class="titulo-elemento" for="formaPago">Forma de pago</label>
                    <input type="text" name="formaPago" id="formaPago" maxlength="120" value="{{ $formaPago }}" required>
                </div>
                <div class="elemento-formulario">
                    <span class="titulo-elemento">¿Tiene padrino?</span>
                    <div class="opciones-en-linea">
                        <label><input type="radio" name="tienePadrino" value="0" {{ $hayPadrino === '0' ? 'checked' : '' }}> No</label>
                        <label><input type="radio" name="tienePadrino" value="1" {{ $hayPadrino === '1' ? 'checked' : '' }}> Sí</label>
                    </div>
                </div>
            </div>
            <div class="linea-formulario linea-separada" id="bloque-padrino">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="padrinoNombre">Nombre del padrino</label>
                    <input type="text" name="padrinoNombre" id="padrinoNombre" maxlength="100" value="{{ old('padrinoNombre', $inscripcion->padrino_nombre) }}">
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="padrinoTelefono">Celular del padrino</label>
                    <input type="tel" name="padrinoTelefono" id="padrinoTelefono" maxlength="20" value="{{ old('padrinoTelefono', $inscripcion->padrino_telefono) }}">
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="padrinoPresente">¿Estará presente?</label>
                    <input type="text" name="padrinoPresente" id="padrinoPresente" maxlength="60" value="{{ old('padrinoPresente', $inscripcion->padrino_presente) }}">
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="padrinoParroquia">Parroquia donde persevera</label>
                    <input type="text" name="padrinoParroquia" id="padrinoParroquia" maxlength="150" value="{{ old('padrinoParroquia', $inscripcion->padrino_parroquia) }}">
                </div>
            </div>
        </fieldset>

        <div class="acciones-formulario">
            <a class="boton-secundario" href="{{ route('inscritos.show', $inscripcion) }}">Cancelar</a>
            <button type="submit" class="boton-azul">Guardar cambios</button>
        </div>
    </form>

<script src="{{ recurso('js/inscritos.js') }}"></script>
@include('partials.footerPanel')
