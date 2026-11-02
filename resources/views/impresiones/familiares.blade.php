@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
            $nombre = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
        @endphp
        <section class="pagina pagina-horizontal familiares">
            <header class="cabecera-familiares">
                <img src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                <div>
                    <p class="dato-negrita">Movimiento de Retiros Parroquiales Juan XXIII · Arquidiócesis de Cali</p>
                    <p>Lista para familiares · {{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</p>
                </div>
                <p class="banda-grupo">{{ $nombre }} <span>Retiro N.º {{ $numero }}</span></p>
            </header>

            {{--  Se llena a lapicero: marcar si vino familia y, si el invitado
                 vino con otro invitado, si es hombre o mujer y su mesa y silla
                 (para juntarlos en la sorpresa del final) --}}
            <table class="tabla-impresion tabla-familiares">
                <colgroup>
                    <col class="col-mesa-familiares"><col class="col-silla-familiares"><col>
                    <col class="col-marcar"><col class="col-marcar"><col class="col-escribir"><col class="col-escribir">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2">Mesa</th>
                        <th rowspan="2">Silla</th>
                        <th rowspan="2">Nombre del invitado</th>
                        <th rowspan="2">¿Vino familia?</th>
                        <th colspan="3" class="titulo-agrupado">Si vino con otro invitado</th>
                    </tr>
                    <tr>
                        <th>Hombre o mujer</th>
                        <th>Mesa</th>
                        <th>Silla</th>
                    </tr>
                </thead>
                @foreach ($grupo['mesas'] as $fila)
                    <tbody class="bloque-familiares">
                        @foreach ($fila['inscritos'] as $inscrito)
                            <tr>
                                @if ($loop->first)
                                    <td class="celda-mesa-familiares" rowspan="{{ $fila['inscritos']->count() }}">
                                        <span class="numero-mesa-directorio">{{ $fila['mesa']->numero }}</span>
                                        @if ($fila['mesa']->nombre)
                                            <span class="nombre-mesa-directorio">{{ $fila['mesa']->nombre }}</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="centrado">{{ $inscrito->silla }}</td>
                                <td>{{ $inscrito->persona->nombre_completo }}</td>
                                <td class="centrado">@include('impresiones.casillas', ['opciones' => ['Sí', 'No']])</td>
                                <td class="centrado">@include('impresiones.casillas', ['opciones' => ['H', 'M']])</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </section>
    @empty
        <div class="pagina pagina-vacia">
            <p>Todavía nadie tiene mesa y silla en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
