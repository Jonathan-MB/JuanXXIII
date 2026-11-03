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


// ============ BUSCAR Y FILTRAR INVITADOS ============

//  Filtra la tabla mientras se escribe (nombre, apellidos, cédula y
// padrino) y según el botón elegido: todos, los que faltan por llegar o
// los que no tienen mesa y silla.
const buscarPuesto = document.getElementById('buscar-puesto');
let filtro         = 'todos';



function pasaFiltro(fila) {
    if (filtro === 'faltan') {
        return fila.dataset.llego === '0';
    }

    if (filtro === 'sin-puesto') {
        return fila.dataset.puesto === '0';
    }

    return true;
}



function filtrarInvitados() {
    const texto = buscarPuesto.value.trim().toLowerCase();
    let visibles = 0;

    document.querySelectorAll('.fila-puesto').forEach((fila) => {
        const mostrar = fila.dataset.buscar.includes(texto) && pasaFiltro(fila);
        fila.hidden = ! mostrar;

        // Si se esconde la persona, también su formulario abierto
        if (! mostrar) {
            cerrarEdicion(fila.querySelector('.boton-editar-datos').dataset.editar);
        }

        if (mostrar) {
            visibles++;
        }
    });

    document.getElementById('puestos-sin-resultados').hidden = visibles > 0;
}



if (buscarPuesto) {
    buscarPuesto.addEventListener('input', filtrarInvitados);

    document.querySelectorAll('.boton-filtro').forEach((boton) => {
        boton.addEventListener('click', () => {
            filtro = boton.dataset.filtro;

            document.querySelectorAll('.boton-filtro').forEach((otro) => {
                otro.classList.toggle('boton-filtro-activo', otro === boton);
            });

            filtrarInvitados();
        });
    });
}


// ============ EDITAR DATOS ============

//  "Editar datos" abre, debajo de la persona, el formulario con los datos
// de la hoja de la mesa. Otro clic (o Cancelar) lo cierra.
function cerrarEdicion(id) {
    const fila  = document.getElementById(id);
    const boton = document.querySelector(`[data-editar="${id}"]`);

    if (fila) {
        fila.hidden = true;
    }

    if (boton) {
        boton.setAttribute('aria-expanded', 'false');
    }
}



document.querySelectorAll('.boton-editar-datos').forEach((boton) => {
    boton.addEventListener('click', () => {
        const fila    = document.getElementById(boton.dataset.editar);
        const abierta = ! fila.hidden;

        fila.hidden = abierta;
        boton.setAttribute('aria-expanded', abierta ? 'false' : 'true');

        if (! abierta) {
            fila.querySelector('input').focus();
        }
    });
});

document.querySelectorAll('[data-cerrar]').forEach((boton) => {
    boton.addEventListener('click', () => cerrarEdicion(boton.dataset.cerrar));
});


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
