@include('partials.headImpresion')

    @php
        $hojas = 0;
    @endphp

    @foreach ($grupos as $sexo => $grupo)
        @php
            $numero = $sexo === 'Femenino' ? $retiro->numero_mujeres : $retiro->numero_hombres;
        @endphp
        @foreach ($grupo['mesas'] as $fila)
            @php
                $hojas++;
            @endphp
            @include('impresiones.planoMesa', [
                'mesa'     => $fila['mesa'],
                'porSilla' => $fila['inscritos']->keyBy(fn ($inscrito) => (int) $inscrito->silla),
            ])
        @endforeach
    @endforeach

    @if ($hojas === 0)
        <div class="pagina pagina-vacia">
            <p>No hay mesas con personas sentadas en este grupo. Asigna los puestos en Llegada y puestos.</p>
        </div>
    @endif

@include('partials.footerImpresion')
