@include('partials.headImpresion', ['volverTexto' => 'Volver al inventario'])

    @forelse ($equipos as $equipo)
        @php
            $porComprar = $equipo->articulos->where('por_comprar', '>', 0);
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Inventario · Equipo ' . $equipo->nombre . ' · ' . now()->locale('es')->translatedFormat('j \d\e F \d\e Y')])

            @if ($equipo->articulos->isEmpty())
                <p class="instruccion">Este equipo todavía no tiene artículos registrados.</p>
            @else
                <p class="instruccion">Cuenten lo que hay y escriban la cantidad en «Conteo». Si no coincide con lo registrado, anótenlo en observaciones.</p>

                <table class="tabla-impresion tabla-inventario">
                    <thead>
                        <tr>
                            <th>Artículo</th>
                            <th class="columna-numero">Registrado</th>
                            <th>Unidad</th>
                            <th>Estado</th>
                            <th class="columna-numero">Por comprar</th>
                            <th class="columna-conteo">Conteo</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($equipo->articulos as $articulo)
                            <tr>
                                <td>{{ $articulo->nombre }}</td>
                                <td class="columna-numero">{{ $articulo->cantidad }}</td>
                                <td>{{ $articulo->unidad }}</td>
                                <td>{{ $articulo->estado }}</td>
                                <td class="columna-numero">{{ $articulo->por_comprar ?: '' }}</td>
                                <td class="columna-conteo"></td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($porComprar->isNotEmpty())
                    <p class="resumen-comprar">
                        <strong>Por comprar ({{ $porComprar->count() }}):</strong>
                        {{ $porComprar->map(fn ($articulo) => $articulo->nombre . ' — ' . $articulo->por_comprar . ' ' . $articulo->unidad)->implode(' · ') }}
                    </p>
                @endif
            @endif

            <div class="firmas-inventario">
                <div><span class="linea-firma"></span><p>Entregó · Dirigente del equipo</p></div>
                <div><span class="linea-firma"></span><p>Recibió · Dirigente diocesano</p></div>
            </div>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>Ningún equipo tiene artículos registrados.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
