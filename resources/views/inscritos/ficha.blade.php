@php
    use App\Models\Inscripcion;
    use App\Models\Rol;

    $persona     = $inscripcion->persona;
    $retiro      = $inscripcion->retiro;
    $puedeEditar = auth()->user()->tieneRol(Rol::ADMINISTRACION, Rol::SECRETARIA);
    $puedePagar  = auth()->user()->tieneRol(Rol::ADMINISTRACION, Rol::TESORERIA);
    $sacramentos = collect(['bautismo' => 'Bautismo', 'primera_comunion' => 'Primera comunión', 'confirmacion' => 'Confirmación', 'matrimonio' => 'Matrimonio'])
        ->filter(fn ($nombre, $campo) => $persona->$campo)
        ->values();
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/inscritos.css') }}">
<title>{{ $persona->nombre_completo }} · Inscritos</title>

@include('partials.header')

    <a class="enlace-volver" href="{{ route('inscritos.index', ['pestana' => $inscripcion->estaFuera() ? 'fuera' : ($persona->sexo === 'Femenino' ? 'mujeres' : 'hombres')]) }}">← Volver a inscritos</a>

    {{-- --------------------CABECERA------------------- --}}
    <section class="cabecera-ficha">
        <div>
            <h1 class="vista-titulo">{{ $persona->nombre_completo }}</h1>
            <p class="ficha-retiro">{{ $persona->sexo === 'Femenino' ? 'Retiro de mujeres' : 'Retiro de hombres' }} N.º {{ $retiro->numeroPara($persona->sexo) }} · {{ $retiro->dias_texto }}</p>
            <div class="chips-ficha">
                <span class="chip {{ $inscripcion->estaFuera() ? 'chip-rojo' : ($inscripcion->estado === 'confirmado' ? 'chip-azul' : 'chip-gris') }}">{{ $inscripcion->nombre_estado }}</span>
                @include('partials.chipPago', ['inscrito' => $inscripcion])
            </div>
        </div>

        @if ($puedeEditar)
            <div class="acciones-ficha">
                <a class="boton-azul" href="{{ route('inscritos.edit', $inscripcion) }}">Editar datos</a>

                @if ($inscripcion->estado === Inscripcion::INSCRITO)
                    <form method="POST" action="{{ route('inscritos.estado', $inscripcion) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="{{ Inscripcion::CONFIRMADO }}">
                        <button type="submit" class="boton-secundario">Confirmar datos</button>
                    </form>
                @endif

                @if ($inscripcion->estaFuera())
                    <form method="POST" action="{{ route('inscritos.estado', $inscripcion) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="{{ Inscripcion::INSCRITO }}">
                        <button type="submit" class="boton-secundario">Devolver a la lista</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('inscritos.estado', $inscripcion) }}" class="formulario-confirmar" data-confirmar="{{ $persona->nombres }} saldrá de las listas del retiro (mesas, cartas, impresiones). Sus pagos se conservan y se puede deshacer. ¿Continuar?">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="{{ Inscripcion::NO_ASISTIO }}">
                        <button type="submit" class="boton-peligro">Marcar que no asistió</button>
                    </form>
                    <form method="POST" action="{{ route('inscritos.estado', $inscripcion) }}" class="formulario-confirmar" data-confirmar="{{ $persona->nombres }} quedará como que abandonó el retiro: no recibe carta y podrá inscribirse en otro retiro. ¿Continuar?">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="estado" value="{{ Inscripcion::ABANDONO }}">
                        <button type="submit" class="boton-peligro">Marcar que abandonó</button>
                    </form>
                @endif
            </div>
        @endif
    </section>

    @include('partials.mensaje')

    <div class="rejilla-ficha">

        {{-- --------------------DATOS PERSONALES------------------- --}}
        <section class="bloque-ficha">
            <h2 class="titulo-bloque-ficha">Datos personales</h2>
            <dl class="datos-ficha">
                <dt>Cédula</dt><dd>{{ $persona->cedula }}</dd>
                <dt>Nacimiento</dt><dd>{{ $persona->fecha_nacimiento->format('d/m/Y') }} ({{ (int) $persona->fecha_nacimiento->diffInYears($retiro->fecha_inicio) }} años)</dd>
                <dt>Celular</dt><dd>{{ $persona->celular }}</dd>
                <dt>Correo</dt><dd>{{ $persona->correo ?? '—' }}</dd>
                <dt>Dirección</dt><dd>{{ $persona->direccion }}</dd>
                <dt>Barrio</dt><dd>{{ $persona->barrio }}</dd>
                <dt>Ciudad</dt><dd>{{ $persona->ciudad }}</dd>
            </dl>
        </section>

        {{-- --------------------SALUD------------------- --}}
        <section class="bloque-ficha">
            <h2 class="titulo-bloque-ficha">Salud y sacramentos</h2>
            <dl class="datos-ficha">
                <dt>EPS</dt><dd>{{ $persona->eps }}</dd>
                <dt>Medicamentos</dt><dd>{{ $persona->medicamentos ?: 'No toma' }}</dd>
                <dt>Talla</dt><dd>{{ $persona->talla_camiseta }}</dd>
                <dt>Sacramentos</dt><dd>{{ $sacramentos->isEmpty() ? 'Ninguno' : $sacramentos->join(', ', ' y ') }}</dd>
            </dl>
        </section>

        {{-- --------------------CONTACTOS------------------- --}}
        <section class="bloque-ficha">
            <h2 class="titulo-bloque-ficha">Contactos de emergencia</h2>
            @foreach ($inscripcion->contactosEmergencia as $contacto)
                <div class="contacto-ficha">
                    <p class="contacto-nombre">{{ $contacto->nombre }} <span>({{ $contacto->parentesco }})</span></p>
                    <p>{{ $contacto->celular }}{{ $contacto->telefono_fijo ? ' · Fijo ' . $contacto->telefono_fijo : '' }}</p>
                    @if ($contacto->correo)
                        <p>{{ $contacto->correo }}</p>
                    @endif
                </div>
            @endforeach
        </section>

        {{-- --------------------INSCRIPCIÓN Y PADRINO------------------- --}}
        <section class="bloque-ficha">
            <h2 class="titulo-bloque-ficha">Inscripción y padrino</h2>
            <dl class="datos-ficha">
                <dt>Inscrito el</dt><dd>{{ $inscripcion->created_at->format('d/m/Y g:i a') }}</dd>
                <dt>Llegada</dt><dd>{{ $inscripcion->llegada_en ? $inscripcion->llegada_en->format('d/m/Y g:i a') : 'Todavía no' }}</dd>
                <dt>Puesto</dt><dd>{{ $inscripcion->mesa ? 'Mesa ' . $inscripcion->mesa->numero . ' · Silla ' . $inscripcion->silla : 'Sin asignar' }}</dd>
                <dt>Forma de pago</dt><dd>{{ $inscripcion->forma_pago }}</dd>
                <dt>Comprobante</dt>
                <dd>
                    @if ($inscripcion->comprobante)
                        <a class="enlace-accion" href="{{ route('inscritos.comprobante', $inscripcion) }}" target="_blank" rel="noopener">Ver comprobante</a>
                    @else
                        No lo subió
                    @endif
                </dd>
                <dt>Padrino</dt>
                <dd>
                    @if ($inscripcion->tiene_padrino)
                        {{ $inscripcion->padrino_nombre }}{{ $inscripcion->padrino_telefono ? ' · ' . $inscripcion->padrino_telefono : '' }}
                        @if ($inscripcion->padrino_presente)
                            <br><small>Presente: {{ $inscripcion->padrino_presente }}</small>
                        @endif
                        @if ($inscripcion->padrino_parroquia)
                            <br><small>Persevera en: {{ $inscripcion->padrino_parroquia }}</small>
                        @endif
                    @else
                        No tiene; se le asigna quien ore por él o ella
                    @endif
                </dd>
            </dl>
        </section>
    </div>

    {{-- --------------------PAGOS------------------- --}}
    <section class="bloque-ficha bloque-pagos" id="pagos">
        <h2 class="titulo-bloque-ficha">Pagos</h2>

        <div class="resumen-pagos">
            <div><p class="cifra-numero">{{ $retiro->ofrenda_texto }}</p><p class="cifra-etiqueta">ofrenda</p></div>
            <div><p class="cifra-numero">{{ '$' . number_format($inscripcion->total_pagado, 0, ',', '.') }}</p><p class="cifra-etiqueta">pagado</p></div>
            <div><p class="cifra-numero">{{ '$' . number_format($inscripcion->saldo, 0, ',', '.') }}</p><p class="cifra-etiqueta">falta</p></div>
        </div>

        @if ($inscripcion->pagos->isEmpty())
            <p class="texto-vacio">Todavía no hay pagos registrados.</p>
        @else
            <div class="contenedor-tabla">
                <table class="tabla-general">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Valor</th>
                            <th>Medio</th>
                            <th>Nota</th>
                            <th>Registró</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inscripcion->pagos->sortBy('fecha') as $pagoFila)
                            <tr>
                                <td>{{ $pagoFila->fecha->format('d/m/Y') }}</td>
                                <td class="dato-valor">{{ $pagoFila->valor_texto }}</td>
                                <td>{{ $pagoFila->medio }}</td>
                                <td>{{ $pagoFila->nota ?? '' }}</td>
                                <td>{{ $pagoFila->usuario?->nombre ?? '—' }}</td>
                                <td class="acciones-pago">
                                    @if ($pagoFila->comprobante)
                                        <a class="enlace-accion" href="{{ route('pagos.comprobante', $pagoFila) }}" target="_blank" rel="noopener">Comprobante</a>
                                    @endif
                                    @if ($puedePagar)
                                        <button type="button" class="boton-texto boton-editar-pago" data-pago="{{ $pagoFila->id }}">Corregir</button>
                                    @endif
                                </td>
                            </tr>
                            @if ($puedePagar)
                                <tr class="fila-editar-pago" id="editar-pago-{{ $pagoFila->id }}" hidden>
                                    <td colspan="6">
                                        <form class="formulario-pago" method="POST" action="{{ route('pagos.update', $pagoFila) }}" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="valor" inputmode="numeric" value="{{ (int) $pagoFila->valor }}" aria-label="Valor" required>
                                            <input type="date" name="fecha" value="{{ $pagoFila->fecha->format('Y-m-d') }}" aria-label="Fecha" required>
                                            <input type="text" name="medio" maxlength="60" value="{{ $pagoFila->medio }}" aria-label="Medio" required>
                                            <input type="text" name="nota" maxlength="255" value="{{ $pagoFila->nota }}" aria-label="Nota" placeholder="Nota">
                                            <input type="file" name="comprobante" accept="image/jpeg,image/png,image/webp,application/pdf" aria-label="Cambiar comprobante">
                                            <button type="submit" class="boton-azul">Guardar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($puedePagar)
            <form class="formulario-pago formulario-pago-nuevo" method="POST" action="{{ route('pagos.store', $inscripcion) }}" enctype="multipart/form-data">
                @csrf
                <p class="titulo-contacto">Registrar un pago</p>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="valor">Valor ($)</label>
                    <input type="text" name="valor" id="valor" inputmode="numeric" placeholder="105000" value="{{ old('valor') }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="fecha">Fecha</label>
                    <input type="date" name="fecha" id="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="medio">Medio</label>
                    <input type="text" name="medio" id="medio" maxlength="60" list="medios-pago" value="{{ old('medio') }}" required>
                    <datalist id="medios-pago">
                        @foreach ($retiro->cuentasPago as $cuenta)
                            <option value="{{ $cuenta->entidad }} {{ $cuenta->numero }}"></option>
                        @endforeach
                        <option value="Efectivo"></option>
                    </datalist>
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="nota">Nota <small>(opcional)</small></label>
                    <input type="text" name="nota" id="nota" maxlength="255" value="{{ old('nota') }}">
                </div>
                <div class="elemento-formulario">
                    <label class="titulo-elemento" for="comprobantePago">Comprobante <small>(opcional)</small></label>
                    <input type="file" name="comprobante" id="comprobantePago" accept="image/jpeg,image/png,image/webp,application/pdf">
                </div>
                <button type="submit" class="boton-amarillo">Registrar pago</button>
            </form>
        @endif
    </section>

<script src="{{ recurso('js/inscritos.js') }}"></script>
@include('partials.footerPanel')
