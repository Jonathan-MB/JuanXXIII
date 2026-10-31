{{-- Hombres / Mujeres: cada uno tiene su propio desarrollo --}}
<nav class="pestanas-desarrollo" aria-label="Grupo">
    <a class="pestana-desarrollo {{ $grupo === 'hombres' ? 'pestana-activa' : '' }}" href="{{ route($ruta, ['sexo' => 'hombres']) }}">Hombres</a>
    <a class="pestana-desarrollo {{ $grupo === 'mujeres' ? 'pestana-activa' : '' }}" href="{{ route($ruta, ['sexo' => 'mujeres']) }}">Mujeres</a>
</nav>
