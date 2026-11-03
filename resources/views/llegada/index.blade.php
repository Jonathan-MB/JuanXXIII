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
            <div class="cifra-llegada cifra-llegaron"><p class="cifra-numero">{{ $llegaron }} <small>de {{ $inscritosGrupo->count() }}</small></p><p>llegaron</p></div>
            <div class="cifra-llegada cifra-faltan"><p class="cifra-numero">{{ $faltan }}</p><p>faltan por llegar</p></div>
            <div class="cifra-llegada cifra-sin-puesto"><p class="cifra-numero">{{ $sinPuesto }}</p><p>sin mesa y silla</p></div>
        </section>

        {{-- --------------------INVITADOS------------------- --}}
        <section class="seccion-puestos">
            <div class="cabecera-puestos">
                <h2 class="subtitulo-vista">Invitados</h2>
                <div class="botones-imprimir-llegada">
                    <a class="boton-secundario" href="{{ route('impresiones.llegada', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Imprimir lista de llegada</a>
                    <a class="boton-secundario" href="{{ route('impresiones.mesasEnBlanco', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Imprimir hojas de mesa en blanco</a>
                </div>
            </div>

            <ol class="pasos-llegada">
                <li>Busca a la persona por su nombre o su cédula.</li>
                <li>Cuando llegue, oprime <strong>Llegó</strong>.</li>
                <li>Escribe su <strong>mesa</strong> y su <strong>silla</strong> y oprime <strong>Guardar</strong>.</li>
                <li>Si un dato está mal, oprime <strong>Editar datos</strong>.</li>
            </ol>

            @if ($inscritosGrupo->isEmpty())
                <p class="texto-vacio">No hay inscritos en este grupo.</p>
            @else
                <div class="filtros-llegada">
                    <input class="buscador buscador-puestos" type="search" id="buscar-puesto" placeholder="Buscar por nombre, cédula o padrino…" aria-label="Buscar invitado" autofocus>
                    <div class="botones-filtro" role="group" aria-label="Mostrar">
                        <button type="button" class="boton-filtro boton-filtro-activo" data-filtro="todos">Todos</button>
                        <button type="button" class="boton-filtro" data-filtro="faltan">Faltan por llegar</button>
                        <button type="button" class="boton-filtro" data-filtro="sin-puesto">Sin mesa y silla</button>
                    </div>
                </div>
                <p class="texto-vacio" id="puestos-sin-resultados" hidden>Nadie coincide con la búsqueda.</p>

                <div class="contenedor-tabla contenedor-llegada">
                    <table class="tabla-general tabla-puestos">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Edad</th>
                                <th>Padrino</th>
                                <th>Llegada</th>
                                <th>Mesa y silla</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Primero quienes faltan por llegar o no tienen puesto --}}
                            @foreach ($inscritosGrupo->sortBy(fn ($inscrito) => [$inscrito->mesa_id ? 1 : 0, $inscrito->mesa?->numero, $inscrito->silla]) as $inscrito)
                                @php
                                    $persona = $inscrito->persona;
                                @endphp
                                <tr class="fila-puesto {{ $inscrito->llegada_en ? 'fila-llego' : '' }}"
                                    data-buscar="{{ mb_strtolower($persona->nombre_completo . ' ' . $persona->cedula . ' ' . $inscrito->padrino_nombre) }}"
                                    data-llego="{{ $inscrito->llegada_en ? 1 : 0 }}"
                                    data-puesto="{{ $inscrito->mesa_id ? 1 : 0 }}">
                                    <td class="celda-nombre">
                                        <a class="enlace-inscrito" href="{{ route('inscritos.show', $inscrito) }}">{{ $persona->apellidos }}, {{ $persona->nombres }}</a>
                                        <button type="button" class="boton-editar-datos" data-editar="editar-{{ $inscrito->id }}" aria-expanded="false">Editar datos</button>
                                    </td>
                                    <td class="dato-sin-salto" data-titulo="Cédula">{{ $persona->cedula }}</td>
                                    <td data-titulo="Edad">{{ (int) $persona->fecha_nacimiento->diffInYears($retiro->fecha_inicio) }}</td>
                                    <td data-titulo="Padrino">{{ $inscrito->tiene_padrino ? $inscrito->padrino_nombre : '—' }}</td>
                                    <td class="celda-llegada">
                                        @if ($inscrito->llegada_en)
                                            <span class="chip chip-verde">Llegó {{ $inscrito->llegada_en->format('g:i a') }}</span>
                                            <form method="POST" action="{{ route('llegada.deshacer', $inscrito) }}" class="formulario-confirmar" data-confirmar="¿Deshacer la llegada de {{ $persona->nombres }}? Su puesto se conserva.">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="boton-texto boton-deshacer">Deshacer</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('llegada.marcar', $inscrito) }}" class="formulario-un-clic">
                                                @csrf
                                                <button type="submit" class="boton-amarillo boton-llego">Llegó</button>
                                            </form>
                                            @if ($inscrito->estado_pago !== 'completo')
                                                <p class="aviso-saldo">Le falta {{ '$' . number_format($inscrito->saldo, 0, ',', '.') }}</p>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="celda-puesto">@include('llegada.puesto', ['inscrito' => $inscrito])</td>
                                </tr>

                                {{-- Se abre con "Editar datos": los mismos datos de la hoja de mesa --}}
                                <tr class="fila-editar" id="editar-{{ $inscrito->id }}" hidden>
                                    <td colspan="6">
                                        <form class="formulario-corregir" method="POST" action="{{ route('llegada.datos', $inscrito) }}">
                                            @csrf
                                            @method('PATCH')
                                            <label>Nombres<input type="text" name="nombres" maxlength="60" value="{{ $persona->nombres }}" required></label>
                                            <label>Apellidos<input type="text" name="apellidos" maxlength="60" value="{{ $persona->apellidos }}" required></label>
                                            <label>Cédula<input type="text" name="cedula" maxlength="20" inputmode="numeric" data-solo-numeros value="{{ $persona->cedula }}" required></label>
                                            <label>Fecha de nacimiento<input type="date" name="fechaNacimiento" value="{{ $persona->fecha_nacimiento->format('Y-m-d') }}" required></label>
                                            <label>Celular<input type="tel" name="celular" maxlength="20" inputmode="numeric" data-solo-numeros value="{{ $persona->celular }}" required></label>
                                            <label>Ciudad<input type="text" name="ciudad" maxlength="60" value="{{ $persona->ciudad }}" required></label>
                                            <label>Barrio<input type="text" name="barrio" maxlength="60" value="{{ $persona->barrio }}" required></label>
                                            <label>Padrino de retiro <small>(vacío = sin padrino)</small><input type="text" name="padrino" maxlength="100" value="{{ $inscrito->tiene_padrino ? $inscrito->padrino_nombre : '' }}"></label>
                                            <div class="acciones-corregir">
                                                <button type="submit" class="boton-azul">Guardar datos</button>
                                                <button type="button" class="boton-texto" data-cerrar="editar-{{ $inscrito->id }}">Cancelar</button>
                                            </div>
                                        </form>
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
            <p class="texto-ayuda texto-ayuda-puestos">Solo para mirar: en azul las sillas ocupadas y punteadas las libres. Para cambiar a alguien de puesto, búscalo arriba.</p>

            <div class="mapa-mesas" aria-label="Mesas y sillas">
                @forelse ($mesas as $mesa)
                    @php
                        $porSilla = $mesa->inscripciones->keyBy(fn ($inscrito) => (int) $inscrito->silla);
                    @endphp
                    <article class="tarjeta-mesa">
                        <div class="cabecera-mesa">
                            <p class="numero-mesa">Mesa {{ $mesa->numero }}{{ $mesa->nombre ? ' · ' . $mesa->nombre : '' }}</p>
                            <span class="ocupacion-mesa">{{ $porSilla->count() }} de {{ $mesa->cantidad_sillas }}</span>
                        </div>
                        <p class="catequistas-mesa">CT {{ $mesa->catequista_titular ?: '—' }} · CA {{ $mesa->catequista_auxiliar ?: '—' }}</p>
                        <ol class="sillas-mesa">
                            @for ($silla = 1; $silla <= $mesa->cantidad_sillas; $silla++)
                                @if ($porSilla->has($silla))
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
    @endif

<script src="{{ recurso('js/llegada.js') }}"></script>
@include('partials.footerPanel')
