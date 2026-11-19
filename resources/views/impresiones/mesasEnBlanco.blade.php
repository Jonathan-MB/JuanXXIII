@php
    $numeroRetiro = $retiro->numeroPara($sexo);
@endphp
@include('partials.headImpresion')

    {{--  Hoja 2, en blanco: cada persona vuelve a escribir su nombre y sus
         datos de contacto en la fila de su silla, y los catequistas los
         suyos en CT y CA. Con esto Secretaría corrige los datos en Llegada
         («Editar datos»). Una mesa por hoja horizontal, renglones altos. --}}
    @foreach ($mesas as $mesa)
        <section class="pagina pagina-horizontal">
            <div class="mesa-en-blanco">
                @include('impresiones.cabeceraMesaBlanco', ['paso' => 'Hoja 2 · Datos'])

                <table class="tabla-impresion tabla-en-blanco tabla-datos-mesa">
                    <thead>
                        <tr>
                            <th class="columna-silla">Silla</th>
                            <th>Nombres y apellidos</th>
                            <th class="columna-telefono">Celular</th>
                            <th class="columna-ciudad">Ciudad</th>
                            <th class="columna-ciudad">Barrio</th>
                            <th class="columna-contacto">Contacto de emergencia 1<br><small>nombre, parentesco y celular</small></th>
                            <th class="columna-contacto">Contacto de emergencia 2<br><small>nombre, parentesco y celular</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (['CT', 'CA'] as $cargo)
                            <tr class="fila-catequista"><td class="columna-silla">{{ $cargo }}</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                        @endforeach
                        @for ($silla = 1; $silla <= $mesa->cantidad_sillas; $silla++)
                            <tr>
                                <td class="columna-silla">{{ $silla }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
                <p class="pie-lista">Escribe con letra clara, en la fila de tu silla. Los contactos de emergencia son a quienes llamamos si algo pasa durante el retiro.</p>
            </div>
        </section>
    @endforeach

@include('partials.footerImpresion')
