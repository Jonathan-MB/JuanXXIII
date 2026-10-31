// ============ EDITOR: CAMBIOS SIN GUARDAR ============

const formularioActividades = document.getElementById('formulario-actividades');

if (formularioActividades) {
    const barra = document.getElementById('barra-guardar');
    const textoCambios = document.getElementById('texto-cambios');
    let enviando = false;

    // Un campo cambió si es distinto a lo que llegó del servidor
    const campoCambiado = (campo) => {
        if (campo.type === 'hidden') {
            return false;
        }
        if (campo.type === 'checkbox') {
            return campo.checked !== campo.defaultChecked;
        }
        if (campo.tagName === 'SELECT') {
            return [...campo.options].some((opcion) => opcion.selected !== opcion.defaultSelected);
        }
        return campo.value !== campo.defaultValue;
    };

    const revisarCambios = () => {
        let cambiadas = 0;
        let quitadas = 0;

        formularioActividades.querySelectorAll('.fila-actividad').forEach((fila) => {
            const quitar = fila.querySelector('.casilla-quitar input').checked;
            const cambiada = [...fila.querySelectorAll('select, input')].some(campoCambiado);

            fila.classList.toggle('fila-quitada', quitar);
            fila.classList.toggle('fila-cambiada', cambiada && !quitar);
            fila.classList.toggle('fila-charla', fila.querySelector('.casilla-charla input[type="checkbox"]').checked);

            if (quitar) {
                quitadas++;
            } else if (cambiada) {
                cambiadas++;
            }
        });

        const partes = [];
        if (cambiadas) {
            partes.push(cambiadas === 1 ? '1 actividad sin guardar' : cambiadas + ' actividades sin guardar');
        }
        if (quitadas) {
            partes.push(quitadas === 1 ? '1 por quitar' : quitadas + ' por quitar');
        }

        barra.classList.toggle('barra-con-cambios', partes.length > 0);
        textoCambios.textContent = partes.length ? partes.join(' · ') : 'Sin cambios';

        return cambiadas + quitadas;
    };

    formularioActividades.addEventListener('input', revisarCambios);
    formularioActividades.addEventListener('change', revisarCambios);

    // Quitar actividades pide confirmación al guardar
    formularioActividades.addEventListener('submit', (evento) => {
        const quitadas = formularioActividades.querySelectorAll('.fila-quitada').length;

        if (quitadas && !confirm(quitadas === 1 ? '¿Quitar 1 actividad?' : '¿Quitar ' + quitadas + ' actividades?')) {
            evento.preventDefault();
            return;
        }

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



// ============ CONFIRMAR ============

document.querySelectorAll('.formulario-confirmar').forEach((formulario) => {
    formulario.addEventListener('submit', (evento) => {
        if (! confirm(formulario.dataset.confirmar)) {
            evento.preventDefault();
        }
    });
});
