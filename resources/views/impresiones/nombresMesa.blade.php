@php
    $numeroRetiro = $retiro->numeroPara($sexo);
@endphp
@include('partials.headImpresion')

    {{--  Hoja 1, apenas se reparten mesas y sillas: cada persona escribe su
         nombre en la fila de su silla. Con esto Secretaría revisa que los
         nombres estén bien escritos en la aplicación. Dos mesas por hoja. --}}
    @foreach ($mesas->chunk(2) as $par)
        <section class="pagina">
            @foreach ($par as $mesa)
                <div class="mesa-en-blanco">
                    @include('impresiones.cabeceraMesaBlanco', ['paso' => 'Hoja 1 · Nombres'])

                    <table class="tabla-impresion tabla-en-blanco tabla-nombres-mesa">
                        <thead>
                            <tr>
                                <th class="columna-silla">Silla</th>
                                <th>Nombres y apellidos completos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($silla = 1; $silla <= $mesa->cantidad_sillas; $silla++)
                                <tr>
                                    <td class="columna-silla">{{ $silla }}</td>
                                    <td></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                    <p class="pie-lista">Escribe con letra clara tu nombre completo, en la fila de tu silla.</p>
                </div>
            @endforeach
        </section>
    @endforeach

@include('partials.footerImpresion')
