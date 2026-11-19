{{-- Cabecera de las hojas que se llenan a mano en cada mesa: logo, retiro, paso y número de mesa grande --}}
<div class="cabecera-mesa-blanco">
    <img class="logo-mesa-blanco" src="{{ asset('img/logo.png') }}" alt="Logo">
    <div>
        <p class="dato-negrita">Movimiento de Retiros Parroquiales Juan XXIII · Retiro N.º {{ $numeroRetiro }} {{ $sexo === 'Femenino' ? 'Mujeres' : 'Hombres' }}</p>
        <p>{{ $paso }} · {{ $retiro->dias_texto }} de {{ $retiro->fecha_inicio->year }}{{ $mesa->nombre ? ' · ' . $mesa->nombre : '' }}</p>
    </div>
    <p class="numero-mesa-blanco">Mesa {{ $mesa->numero }}</p>
</div>
