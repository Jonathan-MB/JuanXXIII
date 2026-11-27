@php
    //  Cumpleaños con año («15 mar 1990»): solo en los directorios de retiros anteriores
    $cumple = fn ($fecha) => $fecha ? str_replace('.', '', \Carbon\Carbon::parse($fecha)->locale('es')->translatedFormat('j M Y')) : '';
@endphp
@include('partials.headImpresion')

    {{-- Solo en pantalla: directorio.js dice aquí si cada hoja cabe en carta o necesita oficio --}}
    <div class="aviso-carpeta aviso-papel" id="aviso-papel">
        <p>Acomodando el directorio en una sola hoja…</p>
    </div>

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombre = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
        @endphp
        {{--  Todo el grupo en UNA hoja: directorio.js achica la letra y, si
             hace falta, pasa a papel oficio --}}
        <section class="pagina hoja-directorio papel-carta" data-grupo="{{ $nombre }}">
            <div class="contenido-directorio">
                <header class="cabecera-directorio">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                    <div>
                        <p class="dato-negrita">Movimiento de Retiros Parroquiales Juan XXIII · Arquidiócesis de Cali</p>
                        <p>Directorio · {{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }} · {{ $retiro->lugar }}</p>
                    </div>
                    <p class="grupo-directorio">Retiro {{ $numero }}<br>{{ $nombre }}</p>
                </header>

                {{-- --------------------RECTORADO------------------- --}}
                <table class="tabla-directorio tabla-rectorado-directorio">
                    <thead>
                        <tr><th>Rectorado</th><th>Nombres y apellidos</th><th>Ciudad / barrio</th><th>Teléfono</th>@if ($conCumpleanos)<th>Cumpleaños</th>@endif</tr>
                    </thead>
                    <tbody>
                        @forelse ($rectorados[$sexo] ?? [] as $cargo)
                            <tr>
                                <td class="dato-negrita">{{ $cargo->cargo }}</td>
                                <td>{{ $cargo->nombre }}</td>
                                <td>{{ $cargo->ciudad }}</td>
                                <td>{{ $cargo->telefono }}</td>
                                @if ($conCumpleanos)<td>{{ $cumple($cargo->fecha_nacimiento) }}</td>@endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $conCumpleanos ? 5 : 4 }}">Rectorado sin registrar.</td></tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- --------------------MESAS------------------- --}}
                {{-- Cada mesa a todo el ancho; los títulos van una sola vez arriba --}}
                <table class="tabla-directorio tabla-titulos-directorio">
                    <colgroup>
                        <col class="col-mesa"><col class="col-silla"><col><col class="col-barrio"><col class="col-ciudad"><col class="col-telefono">@if ($conCumpleanos)<col class="col-cumple">@endif
                    </colgroup>
                    <thead>
                        <tr><th>Mesa</th><th>Silla</th><th>Nombre</th><th>Barrio</th><th>Ciudad</th><th>Teléfono</th>@if ($conCumpleanos)<th>Cumpleaños</th>@endif</tr>
                    </thead>
                </table>
                @foreach ($grupo['mesas'] as $fila)
                    @php
                        $mesa = $fila['mesa'];
                    @endphp
                    <table class="tabla-directorio tabla-mesa-directorio">
                        <colgroup>
                            <col class="col-mesa"><col class="col-silla"><col><col class="col-barrio"><col class="col-ciudad"><col class="col-telefono">@if ($conCumpleanos)<col class="col-cumple">@endif
                        </colgroup>
                        <tbody>
                            <tr class="fila-catequista">
                                <td class="celda-mesa" rowspan="{{ $fila['inscritos']->count() + 2 }}">
                                    {{-- Solo el número: el nombre del santo no va en el directorio --}}
                                    <span class="numero-mesa-directorio">{{ $mesa->numero }}</span>
                                </td>
                                <td>CT</td>
                                <td>{{ $mesa->catequista_titular }}</td>
                                <td>{{ $mesa->titular_barrio }}</td>
                                <td>{{ $mesa->titular_ciudad }}</td>
                                <td>{{ $mesa->titular_celular }}</td>
                                @if ($conCumpleanos)<td>{{ $cumple($mesa->titular_nacimiento) }}</td>@endif
                            </tr>
                            <tr class="fila-catequista">
                                <td>CA</td>
                                <td>{{ $mesa->catequista_auxiliar }}</td>
                                <td>{{ $mesa->auxiliar_barrio }}</td>
                                <td>{{ $mesa->auxiliar_ciudad }}</td>
                                <td>{{ $mesa->auxiliar_celular }}</td>
                                @if ($conCumpleanos)<td>{{ $cumple($mesa->auxiliar_nacimiento) }}</td>@endif
                            </tr>
                            @foreach ($fila['inscritos'] as $inscrito)
                                <tr>
                                    <td>{{ $inscrito->silla }}</td>
                                    <td>{{ $inscrito->persona->nombre_completo }}</td>
                                    <td>{{ $inscrito->persona->barrio }}</td>
                                    <td>{{ $inscrito->persona->ciudad }}</td>
                                    <td>{{ $inscrito->persona->celular }}</td>
                                    @if ($conCumpleanos)<td>{{ $cumple($inscrito->persona->fecha_nacimiento) }}</td>@endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach

                {{-- --------------------PERSEVERANCIAS------------------- --}}
                {{-- Siempre sale: aunque no haya perseverancias, va el Cuarto día --}}
                    <div class="bloque-perseverancias">
                        <p class="titulo-perseverancias">Hermano retirista, persevera y fórmate. Asiste a:</p>
                        <div class="cuerpo-perseverancias">
                        <div class="lista-perseverancias">
                        @php
                            //  La escuela va destacada junto al Cuarto día; las perseverancias, cada una con su barrio de título
                            [$especiales, $gruposPerseverancia] = $perseverancias->partition(fn ($item) => mb_strtolower($item->tipo) !== 'perseverancia');
                        @endphp
                        {{-- El Cuarto día siempre sale; lo que falte queda en blanco para escribirlo --}}
                        <div class="especiales-perseverancias">
                                <div class="perseverancia-especial cuarto-dia-directorio">
                                    <p class="tipo-especial">Cuarto día</p>
                                    <p class="cuando-perseverancia">
                                        @if ($cuartoDia['fecha'] || $cuartoDia['hora'])
                                            {{ collect([$cuartoDia['fecha'], $cuartoDia['hora']])->filter()->join(' · ') }}
                                        @else
                                            <span class="espacio-cuarto-dia"></span>
                                        @endif
                                    </p>
                                    <p class="lugar-perseverancia">
                                        @if ($cuartoDia['lugar'])
                                            <strong>{{ $cuartoDia['lugar'] }}</strong>
                                        @else
                                            <span class="espacio-cuarto-dia espacio-largo-cuarto"></span>
                                        @endif
                                    </p>
                                </div>
                                @foreach ($especiales as $perseverancia)
                                    <div class="perseverancia-especial">
                                        <p class="tipo-especial">{{ $perseverancia->tipo }}</p>
                                        <p class="cuando-perseverancia">{{ $perseverancia->dia }} · {{ $perseverancia->hora }}</p>
                                        <p class="lugar-perseverancia">
                                            <strong>{{ $perseverancia->parroquia }}</strong>
                                            @if ($perseverancia->direccion)
                                                <span>{{ $perseverancia->direccion }}</span>
                                            @endif
                                        </p>
                                    </div>
                                @endforeach
                        </div>
                        @if ($gruposPerseverancia->isNotEmpty())
                            <p class="subtitulo-perseverancias">Grupos de perseverancia</p>
                            <div class="rejilla-perseverancias">
                                @foreach ($gruposPerseverancia as $perseverancia)
                                    <div class="perseverancia">
                                        <p class="sector-perseverancia">{{ $perseverancia->sector ?: $perseverancia->parroquia }}</p>
                                        <p class="cuando-perseverancia">{{ $perseverancia->dia }} · {{ $perseverancia->hora }}</p>
                                        <p class="parroquia-perseverancia">{{ $perseverancia->parroquia }}</p>
                                        @if ($perseverancia->direccion)
                                            <p class="perseverancia-direccion">{{ $perseverancia->direccion }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        </div>
                        {{-- El QR de WhatsApp va aquí mismo, a la derecha, para no robarle espacio a las mesas --}}
                        @if ($tieneQr)
                            <div class="whatsapp-perseverancias">
                                <p class="titulo-whatsapp">WhatsApp</p>
                                <img class="qr-whatsapp" src="{{ $qrWhatsapp }}" alt="Código QR del grupo de WhatsApp">
                                <p>Escanéalo para unirte al grupo del movimiento</p>
                            </div>
                        @endif
                        </div>
                    </div>

                {{-- --------------------WHATSAPP------------------- --}}
                {{-- Aparte solo si hay enlace pero no QR (el QR va en el recuadro de perseverancias) --}}
                @if ($whatsapp && ! $tieneQr)
                    <div class="bloque-whatsapp">
                        @if ($tieneQr)
                            <img class="qr-whatsapp" src="{{ $qrWhatsapp }}" alt="Código QR del grupo de WhatsApp">
                        @endif
                        <div class="texto-whatsapp">
                            <p class="titulo-whatsapp">Grupo de WhatsApp</p>
                            <p class="nombre-grupo-whatsapp">Movimiento Juan XXIII · Arquidiócesis de Cali</p>
                            @if ($tieneQr)
                                <p>Escanea este código con la cámara de WhatsApp para unirte al grupo.</p>
                            @endif
                            @if ($whatsapp)
                                <p class="enlace-whatsapp">{{ $whatsapp }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>Todavía nadie tiene mesa y silla en este grupo.</p>
        </div>
    @endforelse

<script src="{{ recurso('js/directorio.js') }}"></script>
@include('partials.footerImpresion')
