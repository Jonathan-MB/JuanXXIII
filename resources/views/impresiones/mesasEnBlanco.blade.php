@php
    $numeroRetiro = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
@endphp
@include('partials.headImpresion')

    {{--  Dos mesas por hoja. Al llegar, cada persona escribe sus datos en la
         fila de su silla, y los catequistas los suyos en CT y CA; después
         Secretaría los pasa a la aplicación. --}}
    @foreach ($mesas->chunk(2) as $par)
        <section class="pagina">
            @foreach ($par as $mesa)
                <div class="mesa-en-blanco">
                    <div class="cabecera-mesa-blanco">
                        <img class="logo-mesa-blanco" src="{{ asset('img/logo.png') }}" alt="Logo">
                        <div>
                            <p class="dato-negrita">Movimiento de Retiros Parroquiales Juan XXIII · Retiro N.º {{ $numeroRetiro }} {{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }}</p>
                            <p>{{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}{{ $mesa->nombre ? ' · ' . $mesa->nombre : '' }}</p>
                        </div>
                        <p class="numero-mesa-blanco">Mesa {{ $mesa->numero }}</p>
                    </div>

                    <table class="tabla-impresion tabla-en-blanco">
                        <thead>
                            <tr>
                                <th class="columna-silla">Silla</th>
                                <th>Nombres y apellidos</th>
                                <th class="columna-cedula">Documento</th>
                                <th class="columna-fecha">Fecha de nac.</th>
                                <th class="columna-telefono">Celular</th>
                                <th class="columna-padrino">Padrino de retiro</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="fila-catequista"><td class="columna-silla">CT</td><td></td><td></td><td></td><td></td><td></td></tr>
                            <tr class="fila-catequista"><td class="columna-silla">CA</td><td></td><td></td><td></td><td></td><td></td></tr>
                            @for ($silla = 1; $silla <= $mesa->cantidad_sillas; $silla++)
                                <tr>
                                    <td class="columna-silla">{{ $silla }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                    <p class="pie-lista">Escribe con letra clara tu nombre completo y tu documento, tal como aparecen en tu cédula.</p>
                </div>
            @endforeach
        </section>
    @endforeach

@include('partials.footerImpresion')
