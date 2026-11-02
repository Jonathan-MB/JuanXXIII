{{-- Letreros para la pared: mesas elegidas o un lugar ("Rectorado") --}}
<article class="tarjeta-impresion tarjeta-letreros">
    <h2 class="nombre-impresion">Letreros para la pared</h2>
    <p class="descripcion-impresion">Uno por hoja carta, con el logo: azul para hombres y vino para mujeres. Para mesas («Mesa 1», «Mesa 2»…) o para marcar un lugar («Rectorado»).</p>
    <form class="formulario-letreros" method="GET" action="{{ route('impresiones.letrerosMesa') }}" target="_blank">
        <select name="sexo" aria-label="Grupo">
            <option value="">Hombres y mujeres</option>
            <option value="hombres">Hombres</option>
            <option value="mujeres">Mujeres</option>
        </select>
        <input type="text" name="mesas" maxlength="60" placeholder="Mesas: 1-12 o 1, 3, 5 (vacío = todas)" aria-label="Mesas">
        <input type="text" name="lugar" maxlength="40" list="lugares-letrero" placeholder="O un lugar: Rectorado…" aria-label="Lugar">
        <datalist id="lugares-letrero">
            <option value="Rectorado"></option>
            <option value="Hombres"></option>
            <option value="Mujeres"></option>
            <option value="Secretaría"></option>
            <option value="Capilla"></option>
            <option value="Baños"></option>
            <option value="Comedor"></option>
        </datalist>
        <button type="submit" class="boton-azul">Ver letreros</button>
    </form>
</article>
