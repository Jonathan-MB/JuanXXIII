@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        <section class="pagina directorio">
            @include('partials.membrete')

            <div class="datos-directorio">
                <p>{{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }} · {{ $retiro->lugar }}</p>
                <p class="numero-directorio">Retiro N.º {{ $numero }} · {{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }}</p>
            </div>

            {{-- --------------------RECTORADO------------------- --}}
            <table class="tabla-impresion tabla-rectorado">
                <thead>
                    <tr><th>Rectorado</th><th>Nombres y apellidos</th><th>Ciudad</th><th>Teléfono</th></tr>
                </thead>
                <tbody>
                    @forelse ($rectorados[$sexo] ?? [] as $cargo)
                        <tr>
                            <td class="dato-negrita">{{ $cargo->cargo }}</td>
                            <td>{{ $cargo->nombre }}</td>
                            <td>{{ $cargo->ciudad }}</td>
                            <td>{{ $cargo->telefono }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Rectorado sin registrar.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- --------------------MESAS------------------- --}}
            @foreach ($grupo['mesas'] as $fila)
                <table class="tabla-impresion bloque-mesa">
                    <caption>Mesa {{ $fila['mesa']->numero }}{{ $fila['mesa']->nombre ? ' · ' . $fila['mesa']->nombre : '' }}</caption>
                    <thead>
                        <tr>
                            <th class="columna-silla">Silla</th>
                            <th>Nombres y apellidos</th>
                            <th>Dirección</th>
                            <th>Teléfono</th>
                            <th>Fecha nac.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="fila-catequista"><td>CT</td><td colspan="4">{{ $fila['mesa']->catequista_titular }}</td></tr>
                        <tr class="fila-catequista"><td>CA</td><td colspan="4">{{ $fila['mesa']->catequista_auxiliar }}</td></tr>
                        @foreach ($fila['inscritos'] as $inscrito)
                            <tr>
                                <td class="columna-silla">{{ $inscrito->silla }}</td>
                                <td>{{ $inscrito->persona->nombre_completo }}</td>
                                <td>{{ $inscrito->persona->ciudad }} / {{ $inscrito->persona->barrio }}</td>
                                <td>{{ $inscrito->persona->celular }}</td>
                                <td>{{ $inscrito->persona->fecha_nacimiento->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            @include('impresiones.sinPuesto', ['sinPuesto' => $grupo['sinPuesto']])

            {{-- --------------------PERSEVERANCIAS------------------- --}}
            @if ($perseverancias->isNotEmpty())
                <div class="bloque-perseverancias">
                    <p class="titulo-perseverancias">Hermano retirista, persevera y fórmate. Asiste a:</p>
                    <div class="rejilla-perseverancias">
                        @foreach ($perseverancias as $perseverancia)
                            <div class="perseverancia">
                                <p class="perseverancia-tipo">{{ $perseverancia->tipo }}</p>
                                <p class="dato-negrita">{{ $perseverancia->dia }} · {{ $perseverancia->hora }}</p>
                                <p>{{ $perseverancia->parroquia }}</p>
                                @if ($perseverancia->direccion)
                                    <p class="perseverancia-direccion">{{ $perseverancia->direccion }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- --------------------WHATSAPP------------------- --}}
            @if ($whatsapp || $tieneQr)
                <div class="bloque-whatsapp">
                    @if ($tieneQr)
                        <img class="qr-whatsapp" src="{{ route('ajustes.qr') }}" alt="Código QR del grupo de WhatsApp">
                    @endif
                    <p>
                        Únete al grupo de WhatsApp del movimiento{{ $tieneQr ? ': escanea el código' : '' }}
                        @if ($whatsapp)
                            <br><strong>{{ $whatsapp }}</strong>
                        @endif
                    </p>
                </div>
            @endif
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
