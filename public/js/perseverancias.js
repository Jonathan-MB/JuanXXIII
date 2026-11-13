// ============ CONFIRMAR ANTES DE QUITAR ============

document.querySelectorAll('.formulario-confirmar').forEach((formulario) => {
    formulario.addEventListener('submit', (evento) => {
        if (! confirm(formulario.dataset.confirmar)) {
            evento.preventDefault();
        }
    });
});
