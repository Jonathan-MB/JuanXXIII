// ============ PESTAÑAS: MI EQUIPO / TODO EL HORARIO ============

const horario = document.getElementById('horario-santisimo');

document.querySelectorAll('.pestana-santisimo').forEach((pestana) => {
    pestana.addEventListener('click', () => {
        document.querySelectorAll('.pestana-santisimo').forEach((otra) => otra.classList.remove('pestana-activa'));
        pestana.classList.add('pestana-activa');
        horario.classList.toggle('solo-mias', pestana.dataset.vista === 'mias');

        // En todo el horario, llevar la vista a la franja que va ahora
        const ahora = document.getElementById('franja-ahora');
        if (pestana.dataset.vista === 'todas' && ahora) {
            ahora.scrollIntoView({ block: 'center' });
        }
    });
});



// ============ EDITOR: CAMBIOS SIN GUARDAR ============

const formularioFranjas = document.getElementById('formulario-franjas');

if (formularioFranjas) {
    const barra = document.getElementById('barra-guardar');
    const textoCambios = document.getElementById('texto-cambios');
    let enviando = false;

    // Cuenta las filas que tienen algo distinto a lo que llegó del servidor
    const revisarCambios = () => {
        let cambiadas = 0;

        formularioFranjas.querySelectorAll('.fila-franja').forEach((fila) => {
            const cambiada = [...fila.querySelectorAll('select, input')].some((campo) => {
                if (campo.tagName === 'SELECT') {
                    return [...campo.options].some((opcion) => opcion.selected !== opcion.defaultSelected);
                }
                return campo.value !== campo.defaultValue;
            });

            fila.classList.toggle('fila-cambiada', cambiada);

            // Una franja sin nadie se marca en rojo mientras se edita
            fila.classList.toggle('fila-vacia', fila.querySelector('.campo-cubre').value === '');
            fila.classList.toggle('fila-todos', fila.querySelector('.campo-cubre').value === 'todos');

            if (cambiada) {
                cambiadas++;
            }
        });

        barra.classList.toggle('barra-con-cambios', cambiadas > 0);
        textoCambios.textContent = cambiadas === 0 ? 'Sin cambios' : (cambiadas === 1 ? '1 franja sin guardar' : cambiadas + ' franjas sin guardar');

        return cambiadas;
    };

    formularioFranjas.addEventListener('input', revisarCambios);
    formularioFranjas.addEventListener('change', revisarCambios);
    formularioFranjas.addEventListener('submit', () => {
        enviando = true;
    });

    // Avisar antes de salir si quedó algo sin guardar
    window.addEventListener('beforeunload', (evento) => {
        if (!enviando && revisarCambios() > 0) {
            evento.preventDefault();
            evento.returnValue = '';
        }
    });
}



// ============ IR A LA PRIMERA FRANJA SIN NADIE ============

const irVacia = document.getElementById('ir-vacia');

if (irVacia) {
    irVacia.addEventListener('click', (evento) => {
        evento.preventDefault();

        const vacia = document.querySelector('.fila-vacia');
        if (vacia) {
            vacia.scrollIntoView({ block: 'center' });
            vacia.querySelector('select').focus({ preventScroll: true });
        }
    });
}



// ============ CONFIRMAR ============

// Quitar una franja de la base o volver a copiarla pide confirmación
document.querySelectorAll('.formulario-confirmar').forEach((formulario) => {
    formulario.addEventListener('submit', (evento) => {
        if (! confirm(formulario.dataset.confirmar)) {
            evento.preventDefault();
        }
    });
});
