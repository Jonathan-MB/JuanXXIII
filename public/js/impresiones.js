// ============ PERSEVERANCIAS: FILAS DINÁMICAS ============

//  Índice que no se repite: arranca en la hora en milisegundos y sube
// de a uno, porque dos clics seguidos pueden caer en el mismo ms.
let siguienteIndice = Date.now();

const botonAgregar = document.getElementById('agregar-perseverancia');

if (botonAgregar) {
    botonAgregar.addEventListener('click', () => {
        const plantilla = document.getElementById('plantilla-perseverancia');
        const fila      = plantilla.content.firstElementChild.cloneNode(true);
        const indice    = siguienteIndice++;

        fila.querySelectorAll('input[data-campo]').forEach((input) => {
            input.name = 'perseverancias[' + indice + '][' + input.dataset.campo + ']';
        });

        document.getElementById('filas-perseverancias').appendChild(fila);
        fila.querySelectorAll('input')[1].focus();
    });
}

document.addEventListener('click', (evento) => {
    if (evento.target.classList.contains('boton-quitar-fila')) {
        evento.target.closest('tr').remove();
    }
});
