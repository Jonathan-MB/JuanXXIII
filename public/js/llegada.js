// ============ CONFIRMAR ACCIONES ============

// Deshacer una llegada o quitar una mesa pide confirmación
document.querySelectorAll('.formulario-confirmar').forEach((formulario) => {
    formulario.addEventListener('submit', (evento) => {
        if (! confirm(formulario.dataset.confirmar)) {
            evento.preventDefault();
        }
    });
});


// ============ PUESTO: MESA Y SILLA ============

//  Se escriben las dos o ninguna (ninguna = quitar el puesto).
// El servidor revalida igual.
document.querySelectorAll('.formulario-puesto').forEach((formulario) => {
    formulario.addEventListener('submit', (evento) => {
        const mesa  = formulario.querySelector('input[name="mesa"]').value.trim();
        const silla = formulario.querySelector('input[name="silla"]').value.trim();

        if ((mesa === '') !== (silla === '')) {
            evento.preventDefault();
            alert('Escribe la mesa y la silla. Para quitar el puesto, deja las dos vacías.');
            return;
        }

        if (mesa === '' && ! confirm('¿Quitar el puesto de esta persona?')) {
            evento.preventDefault();
        }
    });
});


// ============ BUSCAR EN LOS PUESTOS ============

//  Filtra la tabla mientras se escribe, para no tener que hacer scroll
// buscando a alguien. Busca en nombre, apellidos, cédula y padrino.
const buscarPuesto = document.getElementById('buscar-puesto');

if (buscarPuesto) {
    buscarPuesto.addEventListener('input', () => {
        const texto = buscarPuesto.value.trim().toLowerCase();
        let visibles = 0;

        document.querySelectorAll('.fila-puesto').forEach((fila) => {
            const coincide = fila.dataset.buscar.includes(texto);
            fila.hidden = ! coincide;
            if (coincide) {
                visibles++;
            }
        });

        document.getElementById('puestos-sin-resultados').hidden = visibles > 0;
    });
}


// ============ EVITAR DOBLE CLIC ============

//  En la llegada hay afán: un doble clic no debe enviar dos veces
document.querySelectorAll('.formulario-puesto, .formulario-un-clic').forEach((formulario) => {
    formulario.addEventListener('submit', (evento) => {
        if (evento.defaultPrevented) {
            return;
        }
        const boton = formulario.querySelector('button[type="submit"]');
        if (boton) {
            boton.disabled = true;
        }
    });
});
