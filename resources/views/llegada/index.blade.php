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
        <div class="cabecera-vista">
            <h1 class="vista-titulo">Llegada y puestos · {{ $grupo === 'mujeres' ? 'Mujeres · Retiro ' . $retiro->numero_mujeres : 'Hombres · Retiro ' . $retiro->numero_hombres }}</h1>
            <p class="contador-llegada"><strong>{{ $llegaron }}</strong> de {{ $inscritosGrupo->count() }} llegaron · <strong>{{ $sinPuesto->count() }}</strong> sin puesto</p>
        </div>

        <nav class="pestanas-llegada" aria-label="Grupos">
            <a class="pestana {{ $grupo === 'hombres' ? 'pestana-activa' : '' }}" href="{{ route('llegada.index', ['sexo' => 'hombres']) }}">Hombres</a>
            <a class="pestana {{ $grupo === 'mujeres' ? 'pestana-activa' : '' }}" href="{{ route('llegada.index', ['sexo' => 'mujeres']) }}">Mujeres</a>
            <a class="pestana pestana-derecha" href="{{ route('mesas.index', ['sexo' => $grupo]) }}">Nombres y catequistas de las mesas</a>
        </nav>

        @include('partials.mensaje')

        <div class="estructura-llegada">

            {{-- --------------------BUSCAR Y MARCAR LLEGADA------------------- --}}
            <section class="columna-busqueda">
                <form class="buscar-llegada" method="GET" action="{{ route('llegada.index') }}">
                    <input type="hidden" name="sexo" value="{{ $grupo }}">
                    <input class="buscador" type="search" name="busqueda" value="{{ $busqueda }}" placeholder="Cédula o nombre…" aria-label="Cédula o nombre" autofocus>
                    <button type="submit" class="boton-azul">Buscar</button>
                </form>

                @if ($busqueda !== '' && $encontrados->isEmpty())
                    <p class="texto-vacio">Nadie de {{ $grupo }} coincide con "{{ $busqueda }}". Revisa la pestaña del otro grupo o la lista de inscritos.</p>
                @endif

                @foreach ($encontrados as $inscrito)
                    <article class="tarjeta-llegada {{ $inscrito->llegada_en ? 'tarjeta-llego' : '' }}">
                        <p class="nombre-llegada">{{ $inscrito->persona->nombre_completo }}</p>
                        <p class="detalle-llegada">CC {{ $inscrito->persona->cedula }} · @include('partials.chipPago', ['inscrito' => $inscrito])</p>

                        @if ($inscrito->llegada_en)
                            <p class="puesto-actual">Llegó a las {{ $inscrito->llegada_en->format('g:i a') }}</p>
                        @else
                            @if ($inscrito->estado_pago !== 'completo')
                                <p class="aviso-saldo">Le falta pagar {{ '$' . number_format($inscrito->saldo, 0, ',', '.') }}. Avísale a tesorería.</p>
                            @endif
                            <form method="POST" action="{{ route('llegada.marcar', $inscrito) }}" class="formulario-un-clic">
                                @csrf
                                <button type="submit" class="boton-amarillo boton-ancho">Marcar llegada</button>
                            </form>
                        @endif

                        @include('llegada.puesto', ['inscrito' => $inscrito])

                        @if ($inscrito->llegada_en)
                            <form method="POST" action="{{ route('llegada.deshacer', $inscrito) }}" class="formulario-confirmar" data-confirmar="¿Deshacer la llegada de {{ $inscrito->persona->nombres }}? Su puesto se conserva.">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="boton-texto">Deshacer llegada</button>
                            </form>
                        @endif
                    </article>
                @endforeach

                {{-- --------------------FALTAN POR LLEGAR------------------- --}}
                <details class="faltan-llegar" {{ $busqueda === '' ? 'open' : '' }}>
                    <summary>Faltan por llegar ({{ $faltan->count() }})</summary>
                    @if ($faltan->isEmpty())
                        <p class="texto-vacio">Ya llegaron todos.</p>
                    @else
                        <ul class="lista-faltan">
                            @foreach ($faltan as $inscrito)
                                <li>
                                    <a href="{{ route('llegada.index', ['sexo' => $grupo, 'busqueda' => $inscrito->persona->cedula]) }}">{{ $inscrito->persona->apellidos }}, {{ $inscrito->persona->nombres }}</a>
                                    <form method="POST" action="{{ route('llegada.marcar', $inscrito) }}" class="formulario-un-clic">
                                        @csrf
                                        <button type="submit" class="boton-texto">Llegó</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </details>
            </section>

            {{-- --------------------MAPA DE MESAS------------------- --}}
            <section class="mapa-mesas" aria-label="Mesas y sillas">
                @forelse ($mesas as $mesa)
                    @php
                        $porSilla = $mesa->inscripciones->keyBy(fn ($inscrito) => (int) $inscrito->silla);
                    @endphp
                    <article class="tarjeta-mesa">
                        <div class="cabecera-mesa">
                            <p class="numero-mesa">Mesa {{ $mesa->numero }}{{ $mesa->nombre ? ' · ' . $mesa->nombre : '' }}</p>
                            <span class="ocupacion-mesa">{{ $porSilla->count() }} sentados</span>
                        </div>
                        <p class="catequistas-mesa">CT {{ $mesa->catequista_titular ?: '—' }} · CA {{ $mesa->catequista_auxiliar ?: '—' }}</p>
                        <ol class="sillas-mesa">
                            @for ($silla = 1; $silla <= $mesa->cantidad_sillas; $silla++)
                                @if ($porSilla->has($silla))
                                    <li class="silla silla-ocupada" title="{{ $porSilla[$silla]->persona->nombre_completo }}">
                                        <span class="numero-silla">{{ $silla }}</span>
                                        <a href="{{ route('inscritos.show', $porSilla[$silla]) }}">{{ \Illuminate\Support\Str::words($porSilla[$silla]->persona->nombres, 1, '') }} {{ mb_substr($porSilla[$silla]->persona->apellidos, 0, 1) }}.</a>
                                    </li>
                                @else
                                    <li class="silla silla-libre"><span class="numero-silla">{{ $silla }}</span> vacía</li>
                                @endif
                            @endfor
                        </ol>
                    </article>
                @empty
                    <p class="texto-vacio">Todavía no hay mesas. Aparecen solas cuando escribes la mesa y la silla de alguien.</p>
                @endforelse
            </section>
        </div>

        {{-- --------------------PUESTOS DE TODO EL GRUPO------------------- --}}
        <section class="seccion-puestos">
            <div class="cabecera-puestos">
                <h2 class="subtitulo-vista">Puestos de todos ({{ $inscritosGrupo->count() }})</h2>
                <div class="botones-imprimir-llegada">
                    <a class="boton-secundario" href="{{ route('impresiones.llegada', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Imprimir lista de llegada</a>
                    <a class="boton-secundario" href="{{ route('impresiones.mesasEnBlanco', ['sexo' => $grupo]) }}" target="_blank" rel="noopener">Imprimir hojas de mesa en blanco</a>
                </div>
            </div>
            <p class="texto-ayuda texto-ayuda-puestos">Escribe la mesa y la silla de cada persona y guarda. Los puestos son fijos: cambiar o quitar el de alguien no mueve a nadie más. Primero aparecen quienes no tienen puesto.</p>

            @if ($inscritosGrupo->isEmpty())
                <p class="texto-vacio">No hay inscritos en este grupo.</p>
            @else
                <input class="buscador buscador-puestos" type="search" id="buscar-puesto" placeholder="Buscar por nombre, apellido, cédula o padrino…" aria-label="Buscar en la lista de puestos">
                <p class="texto-vacio" id="puestos-sin-resultados" hidden>Nadie coincide con la búsqueda.</p>

                <div class="contenedor-tabla">
                    <table class="tabla-general tabla-puestos">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Documento</th>
                                <th>Edad</th>
                                <th>Padrino</th>
                                <th>Llegada</th>
                                <th>Puesto (mesa · silla)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inscritosGrupo->sortBy(fn ($inscrito) => [$inscrito->mesa_id ? 1 : 0, $inscrito->mesa?->numero, $inscrito->silla]) as $inscrito)
                                @php
                                    $persona = $inscrito->persona;
                                @endphp
                                <tr class="fila-puesto" data-buscar="{{ mb_strtolower($persona->nombre_completo . ' ' . $persona->cedula . ' ' . $inscrito->padrino_nombre) }}">
                                    <td>
                                        <a class="enlace-inscrito" href="{{ route('inscritos.show', $inscrito) }}">{{ $persona->apellidos }}, {{ $persona->nombres }}</a>
                                        <details class="corregir-datos">
                                            <summary>Corregir nombre o cédula</summary>
                                            <form class="formulario-corregir" method="POST" action="{{ route('llegada.datos', $inscrito) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="text" name="nombres" maxlength="60" value="{{ $persona->nombres }}" aria-label="Nombres" placeholder="Nombres" required>
                                                <input type="text" name="apellidos" maxlength="60" value="{{ $persona->apellidos }}" aria-label="Apellidos" placeholder="Apellidos" required>
                                                <input type="text" name="cedula" maxlength="20" inputmode="numeric" data-solo-numeros value="{{ $persona->cedula }}" aria-label="Cédula" placeholder="Cédula" required>
                                                <button type="submit" class="boton-secundario">Guardar</button>
                                            </form>
                                        </details>
                                    </td>
                                    <td class="dato-sin-salto">{{ $persona->cedula }}</td>
                                    <td>{{ (int) $persona->fecha_nacimiento->diffInYears($retiro->fecha_inicio) }}</td>
                                    <td>{{ $inscrito->tiene_padrino ? $inscrito->padrino_nombre : '—' }}</td>
                                    <td>{{ $inscrito->llegada_en ? $inscrito->llegada_en->format('g:i a') : '—' }}</td>
                                    <td>@include('llegada.puesto', ['inscrito' => $inscrito])</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

<script src="{{ recurso('js/llegada.js') }}"></script>
@include('partials.footerPanel')
