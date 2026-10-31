@include('partials.headImpresion')

    @foreach ($grupos as $sexo => $sala)
        @php
            $esMujer   = $sexo === 'Femenino';
            $rectorado = $rectorados->get($sexo, collect());
        @endphp
        <section class="pagina">
            @include('partials.membrete', ['subtitulo' => 'Catequistas por mesa · Retiro N.º ' . $retiro->numeroPara($sexo) . ' ' . ($esMujer ? 'Mujeres' : 'Hombres') . ' · ' . $retiro->dias_texto . ' de ' . $retiro->fecha_inicio->year])
            @include('partials.rectoradoImpresion')

            {{-- Una fila por par de mesas enfrentadas en la U --}}
            <div class="sala-catequistas">
                @foreach ($sala['izquierda'] as $indice => $mesa)
                    @foreach ([$mesa, $sala['derecha'][$indice] ?? null] as $lado)
                        @if ($lado)
                            <table class="tabla-impresion tabla-catequistas">
                                <caption>Mesa {{ $lado->numero }}{{ $lado->nombre ? ' · ' . $lado->nombre : '' }}</caption>
                                <tbody>
                                    <tr>
                                        <th>Catequista titular (CT)</th>
                                        <td>{{ $lado->catequista_titular }}</td>
                                    </tr>
                                    <tr>
                                        <th>Catequista auxiliar (CA)</th>
                                        <td>{{ $lado->catequista_auxiliar }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @else
                            <span></span>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </section>
    @endforeach

@include('partials.footerImpresion')
