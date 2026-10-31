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
                $mesa     = $fila['mesa'];
                $porSilla = $fila['inscritos']->keyBy(fn ($inscrito) => (int) $inscrito->silla);

                //  Como en la hoja de Word: la mitad de las sillas a la derecha
                // (de la más alta arriba a la 1 abajo) y el resto a la izquierda
                // (de arriba hacia abajo). Con 12 sillas: derecha 6…1, izquierda 7…12.
                $total     = max($mesa->cantidad_sillas, (int) $porSilla->keys()->max());
                $mitad     = (int) ceil($total / 2);
                $derecha   = range($mitad, 1);
                $izquierda = $total > $mitad ? range($mitad + 1, $total) : [];
                $filas     = max(count($derecha), count($izquierda));
            @endphp
            <section class="pagina ubicacion">
                @include('partials.membrete', ['subtitulo' => 'Hoja de ubicación'])

                <div class="datos-ubicacion">
                    <p><strong>Retiro N.º {{ $numero }}</strong> · {{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }}</p>
                    <p><strong>Lugar y fecha:</strong> {{ $retiro->lugar }} · {{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}</p>
                    <p><strong>Mesa N.º {{ $mesa->numero }}</strong></p>
                    <p><strong>Nombre de la mesa:</strong> {{ $mesa->nombre ?: '______________________' }}</p>
                </div>

                <div class="plano-mesa">
                    <p class="catequista-plano">CA · {{ $mesa->catequista_auxiliar ?: '______________________' }}</p>

                    <div class="cuerpo-plano">
                        <ol class="lado-plano lado-izquierdo">
                            @for ($i = 0; $i < $filas; $i++)
                                @if (isset($izquierda[$i]))
                                    <li><span class="numero-plano">{{ $izquierda[$i] }}</span> <span class="nombre-plano">{{ $porSilla->has($izquierda[$i]) ? $porSilla[$izquierda[$i]]->persona->nombre_completo : '' }}</span></li>
                                @else
                                    <li class="silla-sin"></li>
                                @endif
                            @endfor
                        </ol>

                        <div class="mesa-plano">
                            <p>Mesa {{ $mesa->numero }}</p>
                        </div>

                        <ol class="lado-plano lado-derecho">
                            @for ($i = 0; $i < $filas; $i++)
                                @if (isset($derecha[$i]))
                                    <li><span class="nombre-plano">{{ $porSilla->has($derecha[$i]) ? $porSilla[$derecha[$i]]->persona->nombre_completo : '' }}</span> <span class="numero-plano">{{ $derecha[$i] }}</span></li>
                                @else
                                    <li class="silla-sin"></li>
                                @endif
                            @endfor
                        </ol>
                    </div>

                    <p class="catequista-plano">CT · {{ $mesa->catequista_titular ?: '______________________' }}</p>
                </div>
            </section>
        @endforeach
    @endforeach

    @if ($hojas === 0)
        <div class="pagina pagina-vacia">
            <p>No hay mesas con personas sentadas en este grupo. Asigna los puestos en Llegada y puestos.</p>
        </div>
    @endif

@include('partials.footerImpresion')
