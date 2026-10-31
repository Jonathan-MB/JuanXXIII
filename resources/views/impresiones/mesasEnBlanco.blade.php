@php
    $numeroRetiro = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
@endphp
@include('partials.headImpresion')

    {{-- Dos mesas por hoja: cada persona escribe sus datos en la fila de su silla --}}
    @foreach ($mesas->chunk(2) as $par)
        <section class="pagina">
            @foreach ($par as $mesa)
                <div class="mesa-en-blanco">
                    <div class="cabecera-mesa-blanco">
                        <img class="logo-mesa-blanco" src="{{ asset('img/logo.png') }}" alt="Logo">
                        <div>
                            <p class="dato-negrita">Movimiento de Retiros Parroquiales Juan XXIII · Retiro N.º {{ $numeroRetiro }} {{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }}</p>
                            <p>{{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</p>
                        </div>
                        <p class="numero-mesa-blanco">Mesa {{ $mesa->numero }}</p>
                    </div>

                    <table class="tabla-impresion tabla-en-blanco">
                        <thead>
                            <tr>
                                <th class="columna-silla">Silla</th>
                                <th>Nombres y apellidos</th>
                                <th class="columna-cedula">Cédula</th>
                                <th class="columna-telefono">Celular</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($silla = 1; $silla <= $mesa->cantidad_sillas; $silla++)
                                <tr>
                                    <td class="columna-silla">{{ $silla }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                    <p class="pie-lista">Escribe con letra clara tu nombre completo y tu cédula, tal como aparecen en tu documento.</p>
                </div>
            @endforeach
        </section>
    @endforeach

@include('partials.footerImpresion')
