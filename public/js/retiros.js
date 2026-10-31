// ============ FILAS DINÁMICAS ============

//  Cada fila nueva usa un índice que no se repite: arranca en la
// hora en milisegundos (mayor que los índices que vienen del servidor)
// y sube de a uno, porque dos clics seguidos pueden caer en el mismo ms.
let siguienteIndice = Date.now();

function agregarFila(idPlantilla, idContenedor, nombreGrupo) {
    const plantilla  = document.getElementById(idPlantilla);
    const contenedor = document.getElementById(idContenedor);
    const fila       = plantilla.content.firstElementChild.cloneNode(true);
    const indice     = siguienteIndice++;

    fila.querySelectorAll('input[data-campo]').forEach((input) => {
        input.name = nombreGrupo + '[' + indice + '][' + input.dataset.campo + ']';
    });

    contenedor.appendChild(fila);
    fila.querySelector('input').focus();
}


document.getElementById('agregar-cuenta').addEventListener('click', () => {
    agregarFila('plantilla-cuenta', 'filas-cuentas', 'cuentasPago');
});

document.getElementById('agregar-contacto').addEventListener('click', () => {
    agregarFila('plantilla-contacto', 'filas-contactos', 'contactosInformes');
});


// Quitar una fila (sirve también para las que se agregan después)
document.getElementById('formulario-retiro').addEventListener('click', (evento) => {
    if (evento.target.classList.contains('boton-quitar')) {
        evento.target.closest('.fila-dinamica').remove();
    }
});


// ============ VALIDACIÓN ============

document.getElementById('formulario-retiro').addEventListener('submit', (evento) => {
    const inicio = document.getElementById('fechaInicio').value;
    const fin    = document.getElementById('fechaFin').value;

    if (inicio && fin && fin < inicio) {
        evento.preventDefault();
        alert('La fecha de cierre no puede ser antes de la fecha de inicio.');
    }
});
