@php
    $grupo = ($sexo ?? 'Masculino') === 'Femenino' ? 'mujeres' : 'hombres';
@endphp
@include('partials.head')

<link rel="stylesheet" href="{{ recurso('css/llegada.css') }}">
<title>Llegada y puestos · Juan XXIII</title>

@include('partials.header')

    @if (! $retiro)
        <h1 class="vista-titulo">Llegada y puestos</h1>
        <p class="texto-vacio">No hay un retiro próximo.</p>
    @else
        <h1 class="vista-titulo">Llegada y puestos · {{ $grupo === 'mujeres' ? 'Mujeres · Retiro ' . $retiro->numero_mujeres : 'Hombres · Retiro ' . $retiro->numero_hombres }}</h1>

        <nav class="pestanas-llegada" aria-label="Grupos">
            <a class="pestana {{ $grupo === 'hombres' ? 'pestana-activa' : '' }}" href="{{ route('llegada.index', ['sexo' => 'hombres']) }}">Hombres</a>
            <a class="pestana {{ $grupo === 'mujeres' ? 'pestana-activa' : '' }}" href="{{ route('llegada.index', ['sexo' => 'mujeres']) }}">Mujeres</a>
            <a class="pestana pestana-derecha" href="{{ route('mesas.index', ['sexo' => $grupo]) }}">Nombres y catequistas de las mesas</a>
        </nav>

        @include('partials.mensaje')

        {{-- --------------------RESUMEN------------------- --}}
        <section class="resumen-llegada">
            <div class="cifra-llegada cifra-llegaron"><p class="cifra-numero" id="cifra-llegaron">{{ $llegaron }}</p><p>llegaron <small>(tienen mesa y silla)</small></p></div>
            <div class="cifra-llegada cifra-faltan"><p class="cifra-numero" id="cifra-faltan">{{ $faltan }}</p><p>faltan por llegar</p></div>
            <div class="cifra-llegada cifra-sin-puesto"><p class="cifra-numero" id="cifra-se-fueron">{{ $seFueron }}</p><p>se fueron</p></div>
        </section>

        {{-- --------------------INVITADOS------------------- --}}
        <section class="seccion-puestos">
            <div class="cabecera-puestos">
                <h2 class="subtitulo-vista">Invitados</h2>
                <div class="botones-imprimir-llegada">
                    <a class="boton-secundario" href="{{ route('impresiones.llegada', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Imprimir lista de llegada</a>
                    <a class="boton-secundario" href="{{ route('impresiones.nombresMesa', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Hoja 1 · Nombres</a>
                    <a class="boton-secundario" href="{{ route('impresiones.mesasEnBlanco', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Hoja 2 · Datos</a>
                    <a class="boton-secundario" href="{{ route('impresiones.correcciones', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Hoja 3 · Correcciones</a>
                </div>
            </div>

            <ol class="pasos-llegada">
                <li>Busca a la persona por su nombre o su documento.</li>
                <li>Escribe su <strong>mesa</strong> y su <strong>silla</strong> (con eso queda como que llegó) y corrige lo que haga falta. Lo demás (EPS, padrino, sacramentos…) está en <strong>Más datos</strong>.</li>
                <li>Oprime <strong>Guardar</strong> (o Enter). Si alguien se va, marca <strong>Se fue</strong>.</li>
            </ol>

            {{-- --------------------AGREGAR PERSONA------------------- --}}
            {{--  Para quien llega sin haberse inscrito: solo nombres y apellidos
                 son obligatorios; lo demás se completa después en su fila. --}}
            <details class="agregar-persona" @if ($errors->any() || old('nombres')) open @endif>
                <summary class="boton-agregar-persona">+ Agregar persona que llegó sin inscribirse</summary>
                <form class="formulario-agregar" method="POST" action="{{ route('llegada.agregar') }}">
                    @csrf
                    <p class="ayuda-agregar">Solo los <strong>nombres</strong> y los <strong>apellidos</strong> son obligatorios. Lo que no sepas, déjalo en blanco y complétalo después en su fila o en «Más datos».</p>
                    <div class="campos-agregar">
                        <label>Grupo
                            <select name="sexo">
                                <option value="Masculino" @selected(old('sexo', $sexo) === 'Masculino')>Hombres</option>
                                <option value="Femenino" @selected(old('sexo', $sexo) === 'Femenino')>Mujeres</option>
                            </select>
                        </label>
                        <label>Nombres *<input type="text" name="nombres" maxlength="60" value="{{ old('nombres') }}" required></label>
                        <label>Apellidos *<input type="text" name="apellidos" maxlength="60" value="{{ old('apellidos') }}" required></label>
                        <label>Documento<input type="text" name="cedula" maxlength="20" value="{{ old('cedula') }}"></label>
                        <label>Fecha de nacimiento<input type="date" name="fechaNacimiento" value="{{ old('fechaNacimiento') }}"></label>
                        <label>Celular<input type="tel" name="celular" maxlength="20" inputmode="numeric" data-solo-numeros value="{{ old('celular') }}"></label>
                        <label>Ciudad<input type="text" name="ciudad" maxlength="60" value="{{ old('ciudad', 'Cali') }}"></label>
                        <label>Barrio<input type="text" name="barrio" maxlength="60" value="{{ old('barrio') }}"></label>
                        <label>Talla
                            <select name="tallaCamiseta">
                                <option value="">—</option>
                                @foreach (\App\Models\Persona::TALLAS as $talla)
                                    <option value="{{ $talla }}" @selected(old('tallaCamiseta') === $talla)>{{ $talla }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    @if ($errors->any())
                        <p class="error-agregar">{{ $errors->first() }}</p>
                    @endif
                    <button type="submit" class="boton-azul">Agregar a la lista</button>
                </form>
            </details>

            @if ($inscritosGrupo->isEmpty())
                <p class="texto-vacio">No hay inscritos en este grupo.</p>
            @else
                <div class="filtros-llegada">
                    <input class="buscador buscador-puestos" type="search" id="buscar-puesto" placeholder="Buscar por nombre, documento o padrino…" aria-label="Buscar invitado" autofocus>
                    <div class="botones-filtro" role="group" aria-label="Mostrar">
                        <button type="button" class="boton-filtro boton-filtro-activo" data-filtro="todos">Todos</button>
                        <button type="button" class="boton-filtro" data-filtro="faltan">Faltan por llegar</button>
                        <button type="button" class="boton-filtro" data-filtro="se-fueron">Se fueron</button>
                    </div>
                </div>
                <p class="texto-vacio" id="puestos-sin-resultados" hidden>Nadie coincide con la búsqueda.</p>

                {{--  Una fila por invitado, ya lista para corregir: cada casilla se
                     escribe directo y «Guardar» la guarda toda sin recargar la
                     página. Los campos van atados al formulario de la última
                     casilla con el atributo form. --}}
                <div class="contenedor-tabla contenedor-llegada">
                    <table class="tabla-general tabla-puestos">
                        <thead>
                            <tr>
                                <th>Nombres · Apellidos</th>
                                <th>Documento · Celular</th>
                                <th>Nacimiento · Talla</th>
                                <th>Ciudad · Barrio</th>
                                <th>Contactos de emergencia <small>(nombre · parentesco · celular)</small></th>
                                <th>Mesa · Silla</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Primero quienes no tienen puesto --}}
                            @foreach ($inscritosGrupo->sortBy(fn ($inscrito) => [$inscrito->mesa_id ? 1 : 0, $inscrito->mesa?->numero, $inscrito->silla]) as $inscrito)
                                @php
                                    $persona   = $inscrito->persona;
                                    $form      = 'fila-' . $inscrito->id;
                                    $contactos = $inscrito->contactosEmergencia->sortBy('id')->values();
                                @endphp
                                @php $seFue = $inscrito->estado === \App\Models\Inscripcion::ABANDONO; @endphp
                                <tr class="fila-puesto {{ $inscrito->mesa_id ? 'fila-llego' : '' }} {{ $seFue ? 'fila-se-fue' : '' }}"
                                    data-buscar="{{ mb_strtolower($persona->nombre_completo . ' ' . $persona->cedula . ' ' . $inscrito->padrino_nombre) }}"
                                    data-se-fue="{{ $seFue ? 1 : 0 }}"
                                    data-puesto="{{ $inscrito->mesa_id ? 1 : 0 }}"
                                    data-form="{{ $form }}">
                                    {{-- Cada casilla lleva dos datos, uno encima del otro --}}
                                    <td class="celda-doble celda-nombre" data-titulo="Nombres y apellidos">
                                        <input class="campo-fila" form="{{ $form }}" type="text" name="nombres" maxlength="60" value="{{ $persona->nombres }}" required placeholder="Nombres" aria-label="Nombres">
                                        <input class="campo-fila" form="{{ $form }}" type="text" name="apellidos" maxlength="60" value="{{ $persona->apellidos }}" required placeholder="Apellidos" aria-label="Apellidos de {{ $persona->nombres }}">
                                    </td>
                                    <td class="celda-doble" data-titulo="Documento y celular">
                                        <input class="campo-fila campo-documento" form="{{ $form }}" type="text" name="cedula" maxlength="20" value="{{ $persona->cedula }}" placeholder="Documento" aria-label="Documento de {{ $persona->nombres }}">
                                        <input class="campo-fila campo-documento" form="{{ $form }}" type="tel" name="celular" maxlength="20" inputmode="numeric" data-solo-numeros value="{{ $persona->celular }}" placeholder="Celular" aria-label="Celular de {{ $persona->nombres }}">
                                    </td>
                                    <td class="celda-doble" data-titulo="Nacimiento y talla">
                                        <input class="campo-fila campo-fecha" form="{{ $form }}" type="date" name="fechaNacimiento" value="{{ $persona->fecha_nacimiento?->format('Y-m-d') }}" aria-label="Fecha de nacimiento de {{ $persona->nombres }}">
                                        <div class="talla-edad">
                                            <select class="campo-fila campo-talla" form="{{ $form }}" name="tallaCamiseta" aria-label="Talla de camiseta de {{ $persona->nombres }}">
                                                <option value="" @selected(! $persona->talla_camiseta)>—</option>
                                                @foreach (\App\Models\Persona::TALLAS as $talla)
                                                    <option value="{{ $talla }}" @selected($persona->talla_camiseta === $talla)>{{ $talla }}</option>
                                                @endforeach
                                            </select>
                                            <span class="edad-fila"><span data-edad>{{ $persona->edadAl($retiro->fecha_inicio) ?? '—' }}</span> años</span>
                                        </div>
                                    </td>
                                    <td class="celda-doble celda-lugar" data-titulo="Ciudad y barrio">
                                        <input class="campo-fila" form="{{ $form }}" type="text" name="ciudad" maxlength="60" value="{{ $persona->ciudad }}" placeholder="Ciudad" aria-label="Ciudad de {{ $persona->nombres }}">
                                        <input class="campo-fila" form="{{ $form }}" type="text" name="barrio" maxlength="60" value="{{ $persona->barrio }}" placeholder="Barrio" aria-label="Barrio de {{ $persona->nombres }}">
                                    </td>
                                    {{-- Un renglón por contacto: nombre, parentesco y celular --}}
                                    <td class="celda-contacto" data-titulo="Contactos de emergencia">
                                        @foreach ([0, 1] as $c)
                                            <div class="contacto-linea">
                                                <input class="campo-fila" form="{{ $form }}" type="text" name="contactos[{{ $c }}][nombre]" maxlength="100" value="{{ $contactos[$c]->nombre ?? '' }}" placeholder="Contacto {{ $c + 1 }}" aria-label="Contacto {{ $c + 1 }} de {{ $persona->nombres }}: nombre">
                                                <input class="campo-fila" form="{{ $form }}" type="text" name="contactos[{{ $c }}][parentesco]" maxlength="45" value="{{ $contactos[$c]->parentesco ?? '' }}" placeholder="Parentesco" aria-label="Contacto {{ $c + 1 }}: parentesco">
                                                <input class="campo-fila" form="{{ $form }}" type="tel" name="contactos[{{ $c }}][celular]" maxlength="20" inputmode="numeric" data-solo-numeros value="{{ $contactos[$c]->celular ?? '' }}" placeholder="Celular" aria-label="Contacto {{ $c + 1 }}: celular">
                                            </div>
                                        @endforeach
                                    </td>
                                    <td class="celda-mesa-silla" data-titulo="Mesa y silla">
                                        <input class="campo-fila campo-numero" form="{{ $form }}" type="number" name="mesa" min="1" max="99" inputmode="numeric" value="{{ $inscrito->mesa?->numero }}" placeholder="M" aria-label="Mesa de {{ $persona->nombres }}">
                                        <input class="campo-fila campo-numero" form="{{ $form }}" type="number" name="silla" min="1" max="{{ \App\Models\Mesa::MAXIMO_SILLAS }}" inputmode="numeric" value="{{ $inscrito->silla }}" placeholder="S" aria-label="Silla de {{ $persona->nombres }}">
                                    </td>
                                    <td class="celda-guardar">
                                        <form id="{{ $form }}" class="formulario-fila" method="POST" action="{{ route('llegada.datos', $inscrito) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="boton-guardar-fila">Guardar</button>
                                        </form>
                                        <form class="formulario-se-fue" method="POST" action="{{ route('llegada.seFue', $inscrito) }}" data-nombre="{{ $persona->nombre_completo }}">
                                            @csrf
                                            @method('PATCH')
                                            <label class="casilla-se-fue"><input type="checkbox" @checked($seFue)> Se fue</label>
                                        </form>
                                        <a class="enlace-mas-datos" href="{{ route('inscritos.edit', ['inscripcion' => $inscrito, 'volver' => 'llegada']) }}">Más datos</a>
                                        <p class="mensaje-fila" role="status" hidden></p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- --------------------CÓMO VAN LAS MESAS------------------- --}}
        <section class="seccion-mapa">
            <h2 class="subtitulo-vista">Cómo van las mesas</h2>
            <p class="texto-ayuda texto-ayuda-puestos">Solo para mirar: en azul las sillas ocupadas, punteadas las libres y en gris las de quien se fue (quedan vacías, no se le dan a nadie más). Para cambiar a alguien de puesto, búscalo arriba.</p>

            <div class="mapa-mesas" aria-label="Mesas y sillas">
                @forelse ($mesas as $mesa)
                    @php
                        $porSilla = $mesa->inscripciones->keyBy(fn ($inscrito) => (int) $inscrito->silla);
                        $sentados = $porSilla->filter(fn ($inscrito) => $inscrito->estado !== \App\Models\Inscripcion::ABANDONO);
                    @endphp
                    <article class="tarjeta-mesa">
                        <div class="cabecera-mesa">
                            <p class="numero-mesa">Mesa {{ $mesa->numero }}{{ $mesa->nombre ? ' · ' . $mesa->nombre : '' }}</p>
                            <span class="ocupacion-mesa">{{ $sentados->count() }} de {{ $mesa->cantidad_sillas }}</span>
                        </div>
                        <p class="catequistas-mesa">CT {{ $mesa->catequista_titular ?: '—' }} · CA {{ $mesa->catequista_auxiliar ?: '—' }}</p>
                        <ol class="sillas-mesa">
                            @for ($silla = 1; $silla <= $mesa->cantidad_sillas; $silla++)
                                @if ($porSilla->has($silla) && ! $sentados->has($silla))
                                    <li class="silla silla-se-fue" title="{{ $porSilla[$silla]->persona->nombre_completo }} se fue: la silla queda vacía">
                                        <span class="numero-silla">{{ $silla }}</span>
                                        <span class="nombre-silla">se fue</span>
                                    </li>
                                @elseif ($porSilla->has($silla))
                                    <li class="silla silla-ocupada" title="{{ $porSilla[$silla]->persona->nombre_completo }}">
                                        <span class="numero-silla">{{ $silla }}</span>
                                        <span class="nombre-silla">{{ \Illuminate\Support\Str::words($porSilla[$silla]->persona->nombres, 1, '') }} {{ mb_substr($porSilla[$silla]->persona->apellidos, 0, 1) }}.</span>
                                    </li>
                                @else
                                    <li class="silla silla-libre"><span class="numero-silla">{{ $silla }}</span> libre</li>
                                @endif
                            @endfor
                        </ol>
                    </article>
                @empty
                    <p class="texto-vacio">Todavía no hay mesas. Aparecen solas cuando escribes la mesa y la silla de alguien.</p>
                @endforelse
            </div>
        </section>

        @if ($esUltimoDia)
            {{-- --------------------AL TERMINAR EL RETIRO------------------- --}}
            <section class="seccion-terminar">
                <h2 class="subtitulo-vista">Al terminar el retiro</h2>
                @if ($porTerminar > 0)
                    <p class="texto-ayuda">Marca que terminaron el retiro las <strong>{{ $porTerminar }}</strong> personas que tienen mesa y silla (hombres y mujeres). Así reciben su carta, quedan en el historial y no se pueden volver a inscribir. Hazlo antes de imprimir las cartas.</p>
                    <form method="POST" action="{{ route('llegada.terminar') }}" class="formulario-confirmar" data-confirmar="{{ $porTerminar }} personas quedarán como que terminaron el retiro. Quien abandonó debe estar marcado antes desde su ficha. ¿Continuar?">
                        @csrf
                        <button type="submit" class="boton-azul">Marcar que terminaron ({{ $porTerminar }})</button>
                    </form>
                @else
                    <p class="texto-ayuda">Ya están marcados todos los que tienen mesa y silla.</p>
                @endif
            </section>
        @endif
    @endif

<script src="{{ recurso('js/llegada.js') }}"></script>
@include('partials.footerPanel')
