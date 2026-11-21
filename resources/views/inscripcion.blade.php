@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/inicio.css') }}">
<link rel="stylesheet" href="{{ recurso('css/inscripcion.css') }}">
<title>Inscripción · Retiros Juan XXIII</title>

@include('partials.headerPublico')

    @if (! $retiro)
        <section class="seccion-inscripcion">
            <h1 class="titulo-seccion">Las inscripciones aún no están abiertas</h1>
            <p class="texto-seccion">Cuando se anuncie el próximo retiro, aquí encontrarás el formulario.</p>
            @if (session('error'))
                <p class="texto-seccion">{{ session('error') }}</p>
            @endif
            <a class="boton-azul" href="{{ route('inicio') }}">Volver al inicio</a>
        </section>
    @else
        @php
            $hayPadrino   = old('tienePadrino');
            $tomaRemedios = old('tomaMedicamentos');
            $contactos    = old('contactosEmergencia', [[], []]);
            $opcionesPago = $retiro->cuentasPago
                ->map(fn ($cuenta) => $cuenta->entidad . ' ' . $cuenta->numero . ' a nombre de ' . $cuenta->titular)
                ->push('Efectivo')
                ->all();
        @endphp

        {{-- --------------------RESUMEN DEL RETIRO------------------- --}}
        <section class="resumen-inscripcion">
            <div class="resumen-inscripcion-contenido">
                <p class="tarjeta-dia-nombre">Inscripción</p>
                <h1 class="resumen-inscripcion-titulo">Retiro del {{ $retiro->dias_texto }}</h1>
                <p class="resumen-inscripcion-texto">{{ $retiro->lugar }} · {{ $retiro->ciudad }}</p>
                <ul class="resumen-inscripcion-lista">
                    <li><strong>Ofrenda:</strong> {{ $retiro->ofrenda_texto }}@if ($retiro->que_incluye) ({{ $retiro->que_incluye }})@endif</li>
                    <li><strong>Reserva tu cupo</strong> con el {{ $retiro->porcentaje_reserva }}% y envía el soporte de pago. No se reservan cupos sin soporte.</li>
                    @if ($retiro->fecha_limite_pago)
                        <li><strong>Fecha límite</strong> para completar el pago: {{ $retiro->fecha_limite_pago->locale('es')->translatedFormat('j \d\e F') }}.</li>
                    @endif
                    <li>Si cancelas después de reservar, el dinero no se devuelve por los gastos del retiro.</li>
                </ul>
            </div>
        </section>

        <section class="contenedor-inscripcion">
            @include('partials.mensaje')

            <form class="formulario-inscripcion" id="formulario-inscripcion" method="POST" action="{{ route('inscripcion.store') }}" enctype="multipart/form-data" novalidate>
                @csrf

                {{-- --------------------PROGRESO------------------- --}}
                <div class="progreso-inscripcion" aria-hidden="true">
                    <span class="barra-paso barra-activa" data-barra="1"></span>
                    <span class="barra-paso" data-barra="2"></span>
                    <span class="barra-paso" data-barra="3"></span>
                </div>
                <p class="texto-paso" id="texto-paso">Paso 1 de 3 · Datos personales</p>


                {{-- --------------------PASO 1: DATOS PERSONALES------------------- --}}
                <fieldset class="paso-inscripcion" data-paso="1">
                    <legend class="titulo-paso">Datos personales</legend>
                    <p class="ayuda-paso">Escribe la información clara y precisa.</p>

                    <div class="elemento-formulario">
                        <span class="titulo-elemento" id="titulo-sexo">Sexo</span>
                        <div class="opciones-tarjeta" role="radiogroup" aria-labelledby="titulo-sexo">
                            <label class="opcion-tarjeta">
                                <input type="radio" name="sexo" value="Masculino" required {{ old('sexo') === 'Masculino' ? 'checked' : '' }}>
                                <span>Hombre</span>
                            </label>
                            <label class="opcion-tarjeta">
                                <input type="radio" name="sexo" value="Femenino" required {{ old('sexo') === 'Femenino' ? 'checked' : '' }}>
                                <span>Mujer</span>
                            </label>
                        </div>
                    </div>

                    <p class="aviso-retiro" id="aviso-retiro" hidden></p>

                    <div class="linea-inscripcion">
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="nombres">Nombres</label>
                            <input type="text" name="nombres" id="nombres" maxlength="60" autocomplete="given-name" value="{{ old('nombres') }}" required>
                        </div>
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="apellidos">Apellidos</label>
                            <input type="text" name="apellidos" id="apellidos" maxlength="60" autocomplete="family-name" value="{{ old('apellidos') }}" required>
                        </div>
                    </div>

                    <div class="linea-inscripcion">
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="cedula">Número de cédula</label>
                            <input type="text" name="cedula" id="cedula" maxlength="20" inputmode="numeric" data-solo-numeros value="{{ old('cedula') }}" required>
                        </div>
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="fechaNacimiento">Fecha de nacimiento</label>
                            <input type="date" name="fechaNacimiento" id="fechaNacimiento" value="{{ old('fechaNacimiento') }}" required>
                        </div>
                    </div>

                    <div class="linea-inscripcion">
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="celular">Celular</label>
                            <input type="tel" inputmode="numeric" data-solo-numeros name="celular" id="celular" maxlength="20" autocomplete="tel" value="{{ old('celular') }}" required>
                        </div>
                    </div>

                    <div class="linea-inscripcion">
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="barrio">Barrio</label>
                            <input type="text" name="barrio" id="barrio" maxlength="60" value="{{ old('barrio') }}" required>
                        </div>
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="ciudad">Ciudad</label>
                            <input type="text" name="ciudad" id="ciudad" maxlength="60" value="{{ old('ciudad', 'Cali') }}" required>
                        </div>
                    </div>
                </fieldset>


                {{-- --------------------PASO 2: SALUD Y CONTACTOS------------------- --}}
                <fieldset class="paso-inscripcion" data-paso="2" hidden>
                    <legend class="titulo-paso">Salud y contactos de emergencia</legend>

                    <div class="elemento-formulario">
                        <span class="titulo-elemento">Sacramentos que has recibido</span>
                        <div class="casillas-sacramentos">
                            <label class="casilla"><input type="checkbox" name="bautismo" value="1" class="casilla-sacramento" {{ old('bautismo') ? 'checked' : '' }}> Bautismo</label>
                            <label class="casilla"><input type="checkbox" name="primeraComunion" value="1" class="casilla-sacramento" {{ old('primeraComunion') ? 'checked' : '' }}> Primera comunión</label>
                            <label class="casilla"><input type="checkbox" name="confirmacion" value="1" class="casilla-sacramento" {{ old('confirmacion') ? 'checked' : '' }}> Confirmación</label>
                            <label class="casilla"><input type="checkbox" name="matrimonio" value="1" class="casilla-sacramento" {{ old('matrimonio') ? 'checked' : '' }}> Matrimonio</label>
                        </div>
                        <div class="atajos-sacramentos">
                            <button type="button" class="boton-texto" id="marcar-todos">Marcar todos</button>
                            <button type="button" class="boton-texto" id="marcar-ninguno">Ninguno</button>
                        </div>
                    </div>

                    <div class="elemento-formulario">
                        <span class="titulo-elemento" id="titulo-medicamentos">¿Tomas medicamentos?</span>
                        <div class="opciones-tarjeta" role="radiogroup" aria-labelledby="titulo-medicamentos">
                            <label class="opcion-tarjeta">
                                <input type="radio" name="tomaMedicamentos" value="0" required {{ $tomaRemedios === '0' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                            <label class="opcion-tarjeta">
                                <input type="radio" name="tomaMedicamentos" value="1" required {{ $tomaRemedios === '1' ? 'checked' : '' }}>
                                <span>Sí</span>
                            </label>
                        </div>
                    </div>

                    <div class="elemento-formulario" id="bloque-medicamentos" {{ $tomaRemedios === '1' ? '' : 'hidden' }}>
                        <label class="titulo-elemento" for="medicamentos">¿Cuáles y en qué horario?</label>
                        <textarea name="medicamentos" id="medicamentos" maxlength="255" rows="2" placeholder="Ejemplo: Losartán, 8 a. m.">{{ old('medicamentos') }}</textarea>
                    </div>

                    <div class="linea-inscripcion">
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="eps">EPS o entidad de salud</label>
                            <input type="text" name="eps" id="eps" maxlength="60" value="{{ old('eps') }}" required>
                        </div>
                        <div class="elemento-formulario">
                            <label class="titulo-elemento" for="tallaCamiseta">Talla de camiseta</label>
                            <select name="tallaCamiseta" id="tallaCamiseta" required>
                                <option value="">Elige tu talla</option>
                                @foreach (\App\Models\Persona::TALLAS as $talla)
                                    <option value="{{ $talla }}" {{ old('tallaCamiseta') === $talla ? 'selected' : '' }}>{{ $talla }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @foreach ([0, 1] as $i)
                        <div class="bloque-contacto">
                            <p class="titulo-contacto">Contacto de emergencia {{ $i + 1 }}</p>
                            <div class="linea-inscripcion">
                                <div class="elemento-formulario">
                                    <label class="titulo-elemento" for="contacto{{ $i }}Nombre">Nombres y apellidos</label>
                                    <input type="text" name="contactosEmergencia[{{ $i }}][nombre]" id="contacto{{ $i }}Nombre" maxlength="100" value="{{ $contactos[$i]['nombre'] ?? '' }}" required>
                                </div>
                                <div class="elemento-formulario">
                                    <label class="titulo-elemento" for="contacto{{ $i }}Parentesco">Parentesco</label>
                                    <input type="text" name="contactosEmergencia[{{ $i }}][parentesco]" id="contacto{{ $i }}Parentesco" maxlength="45" placeholder="Esposa, hijo, amiga..." value="{{ $contactos[$i]['parentesco'] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="linea-inscripcion linea-tres">
                                <div class="elemento-formulario">
                                    <label class="titulo-elemento" for="contacto{{ $i }}Celular">Celular</label>
                                    <input type="tel" inputmode="numeric" data-solo-numeros name="contactosEmergencia[{{ $i }}][celular]" id="contacto{{ $i }}Celular" maxlength="20" value="{{ $contactos[$i]['celular'] ?? '' }}" required>
                                </div>
                                <div class="elemento-formulario">
                                    <label class="titulo-elemento" for="contacto{{ $i }}Fijo">Teléfono fijo <small>(opcional)</small></label>
                                    <input type="tel" inputmode="numeric" data-solo-numeros name="contactosEmergencia[{{ $i }}][telefonoFijo]" id="contacto{{ $i }}Fijo" maxlength="20" value="{{ $contactos[$i]['telefonoFijo'] ?? '' }}">
                                </div>
                                <div class="elemento-formulario">
                                    <label class="titulo-elemento" for="contacto{{ $i }}Correo">Correo <small>(opcional)</small></label>
                                    <input type="email" name="contactosEmergencia[{{ $i }}][correo]" id="contacto{{ $i }}Correo" maxlength="100" value="{{ $contactos[$i]['correo'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </fieldset>


                {{-- --------------------PASO 3: PAGO, PADRINO Y AUTORIZACIONES------------------- --}}
                <fieldset class="paso-inscripcion" data-paso="3" hidden>
                    <legend class="titulo-paso">Pago, padrino y autorizaciones</legend>

                    <div class="elemento-formulario">
                        <span class="titulo-elemento" id="titulo-pago">Forma de pago</span>
                        <div class="opciones-lista" role="radiogroup" aria-labelledby="titulo-pago">
                            @foreach ($opcionesPago as $opcion)
                                <label class="opcion-lista">
                                    <input type="radio" name="formaPago" value="{{ $opcion }}" required {{ old('formaPago') === $opcion ? 'checked' : '' }}>
                                    <span>{{ $opcion }}</span>
                                </label>
                            @endforeach
                            <label class="opcion-lista">
                                <input type="radio" name="formaPago" value="Otro" required {{ old('formaPago') === 'Otro' ? 'checked' : '' }}>
                                <span>Otro</span>
                            </label>
                        </div>
                        <input type="text" name="formaPagoOtro" id="formaPagoOtro" maxlength="100" placeholder="¿Cuál?" value="{{ old('formaPagoOtro') }}" aria-label="Otra forma de pago" {{ old('formaPago') === 'Otro' ? '' : 'hidden' }}>
                    </div>

                    <div class="elemento-formulario">
                        <label class="titulo-elemento" for="comprobante">Foto o PDF del comprobante de pago <small>(opcional)</small></label>
                        <input type="file" name="comprobante" id="comprobante" accept="image/jpeg,image/png,image/webp,application/pdf">
                        <p class="ayuda-campo">Si todavía no lo tienes, puedes enviarlo después al número de la cuenta donde pagaste.</p>
                    </div>

                    <div class="elemento-formulario">
                        <span class="titulo-elemento" id="titulo-padrino">¿Tienes un retirista que te apadrine?</span>
                        <p class="ayuda-campo">Es alguien que ya vivió el retiro y te acompañará en oración. Si no lo tienes, no te preocupes: pediremos a alguien que ore por ti.</p>
                        <div class="opciones-tarjeta" role="radiogroup" aria-labelledby="titulo-padrino">
                            <label class="opcion-tarjeta">
                                <input type="radio" name="tienePadrino" value="1" required {{ $hayPadrino === '1' ? 'checked' : '' }}>
                                <span>Sí</span>
                            </label>
                            <label class="opcion-tarjeta">
                                <input type="radio" name="tienePadrino" value="0" required {{ $hayPadrino === '0' ? 'checked' : '' }}>
                                <span>No</span>
                            </label>
                        </div>
                    </div>

                    <div class="bloque-contacto" id="bloque-padrino" {{ $hayPadrino === '1' ? '' : 'hidden' }}>
                        <div class="linea-inscripcion">
                            <div class="elemento-formulario">
                                <label class="titulo-elemento" for="padrinoNombre">Nombre del padrino</label>
                                <input type="text" name="padrinoNombre" id="padrinoNombre" maxlength="100" value="{{ old('padrinoNombre') }}">
                            </div>
                            <div class="elemento-formulario">
                                <label class="titulo-elemento" for="padrinoTelefono">Celular del padrino <small>(opcional)</small></label>
                                <input type="tel" inputmode="numeric" data-solo-numeros name="padrinoTelefono" id="padrinoTelefono" maxlength="20" value="{{ old('padrinoTelefono') }}">
                            </div>
                        </div>
                        <div class="linea-inscripcion">
                            <div class="elemento-formulario">
                                <label class="titulo-elemento" for="padrinoPresente">¿Estará presente en el retiro?</label>
                                <select name="padrinoPresente" id="padrinoPresente">
                                    <option value="">No sé</option>
                                    @foreach (['Sí, los tres días', 'Sí, en algunos momentos', 'No'] as $opcion)
                                        <option value="{{ $opcion }}" {{ old('padrinoPresente') === $opcion ? 'selected' : '' }}>{{ $opcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="elemento-formulario">
                                <label class="titulo-elemento" for="padrinoParroquia">Parroquia donde persevera <small>(opcional)</small></label>
                                <input type="text" name="padrinoParroquia" id="padrinoParroquia" maxlength="150" value="{{ old('padrinoParroquia') }}">
                            </div>
                        </div>
                    </div>

                    <div class="bloque-autorizaciones">
                        <label class="casilla casilla-autorizacion">
                            <input type="checkbox" name="declaraRequisitos" value="1" required {{ old('declaraRequisitos') ? 'checked' : '' }}>
                            <span>Declaro que soy mayor de 16 años, que no estoy en embarazo y que no tengo enfermedades de manejo psiquiátrico.</span>
                        </label>
                        <label class="casilla casilla-autorizacion">
                            <input type="checkbox" name="aceptaDatos" value="1" required {{ old('aceptaDatos') ? 'checked' : '' }}>
                            <span>Autorizo al Movimiento de Retiros Parroquiales Juan XXIII a tratar mis datos personales para organizar el retiro, según la Ley 1581 de 2012.</span>
                        </label>
                    </div>
                </fieldset>


                {{-- --------------------BOTONES------------------- --}}
                <div class="botones-paso">
                    <button type="button" class="boton-secundario" id="boton-anterior" hidden>Anterior</button>
                    <button type="button" class="boton-azul" id="boton-siguiente">Siguiente</button>
                    <button type="submit" class="boton-amarillo" id="boton-enviar" hidden>Enviar inscripción</button>
                </div>
            </form>
        </section>

        @php
            $datosRetiro = [
                'numeroHombres' => $retiro->numero_hombres,
                'numeroMujeres' => $retiro->numero_mujeres,
                'fechaInicio'   => $retiro->fecha_inicio->format('Y-m-d'),
            ];
        @endphp
        <script>
            const retiroInscripcion = @json($datosRetiro);
            const rutaVerificarCedula = @json(url('/inscripcion/verificar-cedula'));
            const erroresServidor = @json(array_keys($errors->getMessages()));
        </script>
        <script src="{{ recurso('js/inscripcion.js') }}"></script>
    @endif

@include('partials.footer')
