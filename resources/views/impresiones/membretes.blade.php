@include('partials.headImpresion')
@include('impresiones.cantidad')

    {{--  Hoja oficio con el membrete arriba y el resto en blanco para las
         dinámicas. A cada invitado se le entrega con una hoja en blanco más. --}}
    @foreach ($grupos as $grupo => $cantidad)
        @php
            $numero = $grupo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        @for ($copia = 1; $copia <= $cantidad; $copia++)
            <section class="pagina pagina-oficio">
                <header class="membrete-dinamica">
                    <img class="membrete-dinamica-logo" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
                    <div class="membrete-dinamica-texto">
                        <p>Movimiento de Retiros Parroquiales JUAN XXIII</p>
                        <p>Arquidiócesis de Cali</p>
                        <p>RETIRO {{ $numero }}</p>
                        <p>{{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</p>
                    </div>
                    <img class="membrete-dinamica-virgen" src="{{ asset('img/virgen.png') }}" alt="">
                </header>
            </section>
        @endfor
    @endforeach

@include('partials.footerImpresion')
