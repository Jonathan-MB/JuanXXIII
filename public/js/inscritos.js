// ============ CONFIRMAR ACCIONES ============

// Sacar a alguien de la lista pide confirmación antes de enviarse
document.querySelectorAll('.formulario-confirmar').forEach((formulario) => {
    formulario.addEventListener('submit', (evento) => {
        if (! confirm(formulario.dataset.confirmar)) {
            evento.preventDefault();
        }
    });
});


// ============ CORREGIR UN PAGO ============

// Muestra u oculta el formulario para corregir cada pago
document.querySelectorAll('.boton-editar-pago').forEach((boton) => {
    boton.addEventListener('click', () => {
        const fila = document.getElementById('editar-pago-' + boton.dataset.pago);
        fila.hidden = ! fila.hidden;
        boton.textContent = fila.hidden ? 'Corregir' : 'Cerrar';
    });
});


// ============ CAMPOS SEGÚN LA RESPUESTA (formulario de edición) ============

//  Solo existe en la vista de editar: muestra los bloques de
// medicamentos y padrino según lo elegido.
function alternarBloque(nombreRadio, valorQueMuestra, idBloque, idsObligatorios) {
    const bloque = document.getElementById(idBloque);
    if (! bloque) {
        return;
    }

    const elegido = document.querySelector('input[name="' + nombreRadio + '"]:checked');
    const mostrar = elegido !== null && elegido.value === valorQueMuestra;

    bloque.hidden = ! mostrar;
    idsObligatorios.forEach((id) => {
        document.getElementById(id).required = mostrar;
    });
}

function actualizarBloques() {
    alternarBloque('tomaMedicamentos', '1', 'bloque-medicamentos', ['medicamentos']);
    alternarBloque('tienePadrino', '1', 'bloque-padrino', ['padrinoNombre']);
}

document.querySelectorAll('input[name="tomaMedicamentos"], input[name="tienePadrino"]').forEach((opcion) => {
    opcion.addEventListener('change', actualizarBloques);
});

actualizarBloques();
