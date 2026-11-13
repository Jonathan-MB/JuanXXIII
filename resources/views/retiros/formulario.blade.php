@php
    $editando = $retiro->exists;

    $cuentas   = old('cuentasPago', $cuentasPago);
    $contactos = old('contactosInformes', $contactosInformes);
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/retiros.css') }}">
<title>{{ $editando ? 'Editar retiro' : 'Nuevo retiro' }} · Juan XXIII</title>

@include('partials.header')

    <h1 class="vista-titulo">{{ $editando ? 'Editar ' . $retiro->nombre : 'Nuevo retiro' }}</h1>
    @include('partials.mensaje')

    <form class="formulario-retiro" method="POST" action="{{ $editando ? route('retiros.update', $retiro) : route('retiros.store') }}" id="formulario-retiro">
        @csrf
        @if ($editando)
            @method('PUT')
        @endif

        {{-- --------------------NÚMEROS------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Números del retiro</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="numeroHombres">Retiro de hombres N.º</label>
                    <input type="number" min="1" name="numeroHombres" id="numeroHombres" value="{{ old('numeroHombres', $retiro->numero_hombres) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="numeroMujeres">Retiro de mujeres N.º</label>
                    <input type="number" min="1" name="numeroMujeres" id="numeroMujeres" value="{{ old('numeroMujeres', $retiro->numero_mujeres) }}" required>
                </div>
            </div>
        </fieldset>

        {{-- --------------------FECHAS------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Fechas y horas</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="fechaInicio">Empieza (viernes)</label>
                    <input type="date" name="fechaInicio" id="fechaInicio" value="{{ old('fechaInicio', $retiro->fecha_inicio?->format('Y-m-d')) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="horaInicio">Hora de inicio</label>
                    <input type="time" name="horaInicio" id="horaInicio" value="{{ old('horaInicio', substr((string) $retiro->hora_inicio, 0, 5)) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="fechaFin">Termina (domingo)</label>
                    <input type="date" name="fechaFin" id="fechaFin" value="{{ old('fechaFin', $retiro->fecha_fin?->format('Y-m-d')) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="horaFin">Hora de cierre</label>
                    <input type="time" name="horaFin" id="horaFin" value="{{ old('horaFin', substr((string) $retiro->hora_fin, 0, 5)) }}" required>
                </div>
            </div>
        </fieldset>

        {{-- --------------------OFRENDA------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Ofrenda y reserva</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="ofrenda">Ofrenda ($)</label>
                    <input type="text" inputmode="numeric" name="ofrenda" id="ofrenda" placeholder="210000" value="{{ old('ofrenda', $retiro->ofrenda !== null ? (int) $retiro->ofrenda : '') }}" required>
                </div>
                <div class="elemento-formulario elemento-ancho">
                    <label class="titulo-elemento" for="queIncluye">Qué incluye</label>
                    <input type="text" name="queIncluye" id="queIncluye" maxlength="150" placeholder="Alojamiento, alimentación y materiales" value="{{ old('queIncluye', $retiro->que_incluye) }}">
                </div>
            </div>
            <div class="linea-formulario">
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="porcentajeReserva">Reserva con el (%)</label>
                    <input type="number" min="0" max="100" name="porcentajeReserva" id="porcentajeReserva" value="{{ old('porcentajeReserva', $retiro->porcentaje_reserva) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="fechaLimitePago">Fecha límite de pago</label>
                    <input type="date" name="fechaLimitePago" id="fechaLimitePago" value="{{ old('fechaLimitePago', $retiro->fecha_limite_pago?->format('Y-m-d')) }}">
                </div>
            </div>
        </fieldset>

        {{-- --------------------LUGAR------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Lugar</legend>
            <div class="linea-formulario">
                <div class="elemento-formulario elemento-ancho">
                    <label class="titulo-elemento" for="lugar">Lugar</label>
                    <input type="text" name="lugar" id="lugar" maxlength="100" placeholder="Colegio Nuestra Señora de Chiquinquirá" value="{{ old('lugar', $retiro->lugar) }}" required>
                </div>
                <div class="elemento-formulario elemento-ancho">
                    <label class="titulo-elemento" for="direccion">Dirección</label>
                    <input type="text" name="direccion" id="direccion" maxlength="100" value="{{ old('direccion', $retiro->direccion) }}">
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="ciudad">Ciudad</label>
                    <input type="text" name="ciudad" id="ciudad" maxlength="45" value="{{ old('ciudad', $retiro->ciudad) }}" required>
                </div>
            </div>
        </fieldset>

        {{-- --------------------CUENTAS DE PAGO------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Cuentas de pago</legend>
            <div class="filas-dinamicas" id="filas-cuentas">
                @foreach ($cuentas as $i => $cuenta)
                    <div class="fila-dinamica">
                        <input type="text" name="cuentasPago[{{ $i }}][entidad]" value="{{ $cuenta['entidad'] ?? 'Nequi' }}" aria-label="Entidad" placeholder="Nequi">
                        <input type="text" name="cuentasPago[{{ $i }}][numero]" value="{{ $cuenta['numero'] ?? '' }}" aria-label="Número" placeholder="Número">
                        <input type="text" name="cuentasPago[{{ $i }}][titular]" value="{{ $cuenta['titular'] ?? '' }}" aria-label="Titular" placeholder="Titular">
                        <button type="button" class="boton-quitar" aria-label="Quitar esta cuenta">Quitar</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="boton-secundario" id="agregar-cuenta">+ Agregar cuenta</button>
        </fieldset>

        {{-- --------------------CONTACTOS------------------- --}}
        <fieldset class="bloque-formulario">
            <legend>Contactos para informes</legend>
            <div class="filas-dinamicas" id="filas-contactos">
                @foreach ($contactos as $i => $contacto)
                    <div class="fila-dinamica">
                        <input type="text" name="contactosInformes[{{ $i }}][nombre]" value="{{ $contacto['nombre'] ?? '' }}" aria-label="Nombre" placeholder="Nombre">
                        <input type="tel" inputmode="numeric" data-solo-numeros name="contactosInformes[{{ $i }}][celular]" value="{{ $contacto['celular'] ?? '' }}" aria-label="Celular" placeholder="Celular">
                        <button type="button" class="boton-quitar" aria-label="Quitar este contacto">Quitar</button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="boton-secundario" id="agregar-contacto">+ Agregar contacto</button>
        </fieldset>

        {{-- --------------------INSCRIPCIONES------------------- --}}
        <label class="casilla-inscripciones">
            <input type="hidden" name="inscripcionesAbiertas" value="0">
            <input type="checkbox" name="inscripcionesAbiertas" value="1" {{ old('inscripcionesAbiertas', $retiro->inscripciones_abiertas) ? 'checked' : '' }}>
            Abrir las inscripciones en la página pública
        </label>
        <label class="casilla-inscripciones">
            <input type="hidden" name="esProximo" value="0">
            <input type="checkbox" name="esProximo" value="1" {{ old('esProximo', $retiro->es_proximo) ? 'checked' : '' }}>
            Es el próximo retiro: sale en las cartas al presbítero y sus inscripciones se abren solas cuando se cierren las del retiro actual
        </label>
        <label class="casilla-inscripciones">
            <input type="hidden" name="esJovenes" value="0">
            <input type="checkbox" name="esJovenes" value="1" {{ old('esJovenes', $retiro->es_jovenes) ? 'checked' : '' }}>
            Retiro de jóvenes
        </label>

        <div class="acciones-formulario">
            <a class="boton-secundario" href="{{ route('retiros.index') }}">Cancelar</a>
            <button type="submit" class="boton-azul">{{ $editando ? 'Guardar cambios' : 'Crear retiro' }}</button>
        </div>
    </form>

    {{-- Plantillas que el JS clona para agregar filas --}}
    <template id="plantilla-cuenta">
        <div class="fila-dinamica">
            <input type="text" data-campo="entidad" value="Nequi" aria-label="Entidad" placeholder="Nequi">
            <input type="text" data-campo="numero" aria-label="Número" placeholder="Número">
            <input type="text" data-campo="titular" aria-label="Titular" placeholder="Titular">
            <button type="button" class="boton-quitar" aria-label="Quitar esta cuenta">Quitar</button>
        </div>
    </template>
    <template id="plantilla-contacto">
        <div class="fila-dinamica">
            <input type="text" data-campo="nombre" aria-label="Nombre" placeholder="Nombre">
            <input type="tel" inputmode="numeric" data-solo-numeros data-campo="celular" aria-label="Celular" placeholder="Celular">
            <button type="button" class="boton-quitar" aria-label="Quitar este contacto">Quitar</button>
        </div>
    </template>

<script src="{{ recurso('js/retiros.js') }}"></script>
@include('partials.footerPanel')
