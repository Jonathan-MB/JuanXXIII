{{-- Encabezado con el logo: todo lo impreso lo lleva --}}
<header class="membrete">
    <img class="membrete-logo" src="{{ asset('img/logo.png') }}" alt="Logo del Movimiento Juan XXIII">
    <div class="membrete-texto">
        <p class="membrete-movimiento">Movimiento de Retiros Parroquiales Juan XXIII</p>
        <p class="membrete-arquidiocesis">Arquidiócesis de Cali</p>
        @isset($subtitulo)
            <p class="membrete-subtitulo">{{ $subtitulo }}</p>
        @endisset
    </div>
    <span class="membrete-espacio"></span>
</header>
