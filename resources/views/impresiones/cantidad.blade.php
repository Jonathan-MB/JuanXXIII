{{-- Solo en pantalla: cuántas copias salen por grupo y, con un grupo elegido, cambiarlo antes de imprimir --}}
<div class="aviso-carpeta aviso-cantidad">
    <p>
        @foreach ($grupos as $grupo => $cantidad)
            {{ $grupo === 'Femenino' ? 'Mujeres' : 'Hombres' }}: <strong>{{ $cantidad }}</strong> {{ $cantidad === 1 ? 'copia' : 'copias' }}{{ $loop->last ? '.' : ' · ' }}
        @endforeach
        Salen una por invitado (inscritos, confirmados y los que llegaron).
    </p>
    @if ($sexo)
        <form class="formulario-cantidad" method="GET">
            <input type="hidden" name="sexo" value="{{ request('sexo') }}">
            <label>Cambiar a <input type="number" name="cantidad" min="1" max="300" value="{{ reset($grupos) }}" required></label>
            <button type="submit" class="barra-boton">Ver</button>
        </form>
    @endif
</div>
