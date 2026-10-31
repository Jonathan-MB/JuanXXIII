@include('partials.headImpresion')

    @forelse ($grupos as $sexo => $inscritos)
        @php
            $numero = $retiro->numeroPara($sexo);
            $grupo  = $sexo === 'Femenino' ? 'Mujeres' : 'Hombres';
        @endphp

        {{-- 10 rótulos por hoja, para recortar por las líneas --}}
        @foreach ($inscritos->chunk(10) as $hoja)
            <section class="pagina pagina-rotulos">
                @foreach ($hoja as $inscrito)
                    <article class="rotulo-sobre">
                        <img class="rotulo-logo" src="{{ asset('img/logo.png') }}" alt="">
                        <div class="rotulo-texto">
                            <p class="rotulo-titulo">Ayudas de Dios</p>
                            <p class="rotulo-nombre">{{ $inscrito->persona->nombre_completo }}</p>
                            <p class="rotulo-puesto">
                                @if ($inscrito->mesa)
                                    Mesa {{ $inscrito->mesa->numero }} · Silla {{ $inscrito->silla }}
                                @else
                                    Sin mesa asignada
                                @endif
                            </p>
                            <p class="rotulo-retiro">Retiro N.º {{ $numero }} {{ $grupo }}</p>
                        </div>
                    </article>
                @endforeach
            </section>
        @endforeach
    @empty
        <div class="pagina pagina-vacia">
            <p>No hay inscritos en este grupo.</p>
        </div>
    @endforelse

@include('partials.footerImpresion')
