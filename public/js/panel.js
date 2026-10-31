// ============ CRONOGRAMA: ROJO A 1 HORA ============

//  El color se calcula al cargar la página, pero la página puede quedar
// abierta todo el día. Cada minuto se revisa de nuevo cuánto falta.
const MINUTOS_ALERTA = 60;

function revisarTareas() {
    const ahora = Date.now();

    document.querySelectorAll('.tarea[data-limite]').forEach((tarea) => {
        if (tarea.dataset.hecha === '1') {
            return;
        }

        const limite = new Date(tarea.dataset.limite).getTime();
        const alerta = ahora >= limite - MINUTOS_ALERTA * 60 * 1000;

        tarea.classList.toggle('tarea-alerta', alerta);
        tarea.classList.toggle('tarea-pendiente', ! alerta);
    });
}

if (document.querySelector('.tarea')) {
    revisarTareas();
    setInterval(revisarTareas, 60 * 1000);
}


// Un doble clic no debe marcar y desmarcar de una vez
document.querySelectorAll('.formulario-marcar').forEach((formulario) => {
    formulario.addEventListener('submit', () => {
        formulario.querySelector('button').disabled = true;
    });
});
