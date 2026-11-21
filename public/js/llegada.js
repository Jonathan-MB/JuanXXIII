// ============ CONFIRMAR ACCIONES ============

// Marcar que terminaron pide confirmación
document.addEventListener('submit', (evento) => {
    const formulario = evento.target.closest('.formulario-confirmar');

    if (formulario && ! confirm(formulario.dataset.confirmar)) {
        evento.preventDefault();
    }
});


const token = document.querySelector('meta[name="csrf-token"]').content;



//  Envía un formulario por fetch y devuelve [ok, datos]. Si el servidor
// rechaza, datos trae el mensaje y los errores de cada campo.
async function enviar(formulario) {
    try {
        const respuesta = await fetch(formulario.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(formulario),
        });
        const datos = await respuesta.json().catch(() => ({}));

        if (respuesta.status === 419) {
            return [false, { message: 'La sesión venció. Recarga la página.' }];
        }

        return [respuesta.ok, datos];
    } catch (error) {
        return [false, { message: 'No hay conexión con el servidor. Intenta de nuevo.' }];
    }
}


// ============ CADA FILA: CORREGIR Y GUARDAR ============

//  Cada invitado es una fila ya lista para escribir. Al cambiar algo, la
// fila se marca en amarillo y se activa «Guardar»; con Enter también se
// guarda. Se guarda sin recargar: no se pierde la búsqueda ni el lugar.
function camposDe(fila) {
    return [...document.querySelectorAll(`[form="${fila.dataset.form}"]`)];
}



function mensajeFila(fila, texto, tipo) {
    const mensaje = fila.querySelector('.mensaje-fila');

    mensaje.textContent = texto;
    mensaje.className   = 'mensaje-fila mensaje-' + tipo;
    mensaje.hidden      = ! texto;
}



function revisarCambios(fila) {
    const cambiada = camposDe(fila).some((campo) => campo.value !== campo.defaultValue);

    fila.classList.toggle('fila-cambiada', cambiada);
    fila.querySelector('.boton-guardar-fila').disabled = ! cambiada;

    return cambiada;
}



document.querySelectorAll('.fila-puesto').forEach((fila) => {
    const formulario = document.getElementById(fila.dataset.form);
    const boton      = fila.querySelector('.boton-guardar-fila');

    boton.disabled = true;

    camposDe(fila).forEach((campo) => {
        campo.addEventListener('input', () => {
            campo.classList.remove('campo-con-error');
            mensajeFila(fila, '', 'ok');
            revisarCambios(fila);
        });

        // Enter guarda la fila (en vez de enviar sin pasar por fetch)
        campo.addEventListener('keydown', (evento) => {
            if (evento.key === 'Enter') {
                evento.preventDefault();
                formulario.requestSubmit();
            }
        });
    });

    formulario.addEventListener('submit', async (evento) => {
        evento.preventDefault();

        if (! revisarCambios(fila)) {
            return;
        }

        const mesa  = fila.querySelector('[name="mesa"]');
        const silla = fila.querySelector('[name="silla"]');

        if ((mesa.value.trim() === '') !== (silla.value.trim() === '')) {
            mensajeFila(fila, 'Escribe la mesa y la silla (o deja las dos vacías).', 'error');
            (mesa.value.trim() === '' ? mesa : silla).focus();
            return;
        }

        if (mesa.value.trim() === '' && mesa.defaultValue !== '' && ! confirm('¿Quitar la mesa y la silla de esta persona?')) {
            return;
        }

        boton.disabled    = true;
        boton.textContent = 'Guardando…';

        const [ok, datos] = await enviar(formulario);

        boton.textContent = 'Guardar';

        if (! ok) {
            boton.disabled = false;
            camposDe(fila).forEach((campo) => campo.classList.remove('campo-con-error'));

            Object.keys(datos.errors || {}).forEach((nombre) => {
                // contactos.0.celular → contactos[0][celular]
                const campo = nombre.replace(/\.(\d+)\.(\w+)$/, '[$1][$2]');
                fila.querySelectorAll(`[name="${campo}"]`).forEach((elemento) => elemento.classList.add('campo-con-error'));

                if (nombre === 'mesa') {
                    silla.classList.add('campo-con-error');
                }
            });

            const errores = Object.values(datos.errors || {}).flat();
            mensajeFila(fila, errores[0] || datos.message || 'No se pudo guardar.', 'error');
            return;
        }

        // Lo guardado pasa a ser el valor de partida de cada casilla
        camposDe(fila).forEach((campo) => {
            campo.defaultValue = campo.value;
        });

        fila.dataset.buscar = datos.buscar;
        fila.dataset.puesto = datos.puesto ? '1' : '0';
        fila.classList.toggle('fila-llego', datos.puesto);
        recontar();
        fila.querySelector('[data-edad]').textContent = datos.edad;

        revisarCambios(fila);
        fila.classList.add('fila-guardada');
        setTimeout(() => fila.classList.remove('fila-guardada'), 1600);
        mensajeFila(fila, 'Guardado ✓', 'ok');
        setTimeout(() => mensajeFila(fila, '', 'ok'), 3000);
    });
});


// ============ CIFRAS DE ARRIBA ============

//  Llegaron (tienen mesa y silla), faltan por llegar y se fueron: se
// recuentan en la página después de cada cambio.
function recontar() {
    const filas = [...document.querySelectorAll('.fila-puesto')];
    const enLista = filas.filter((fila) => fila.dataset.seFue !== '1');
    const cifras = {
        'cifra-llegaron': enLista.filter((fila) => fila.dataset.puesto === '1').length,
        'cifra-faltan': enLista.filter((fila) => fila.dataset.puesto !== '1').length,
        'cifra-se-fueron': filas.length - enLista.length,
    };

    Object.entries(cifras).forEach(([id, valor]) => {
        const cifra = document.getElementById(id);
        if (cifra) {
            cifra.textContent = valor;
        }
    });
}


// ============ SE FUE ============

//  Marcar «Se fue» tacha el nombre; quitarlo lo devuelve a la lista.
// Quien se fue conserva su silla, que queda vacía.
document.querySelectorAll('.formulario-se-fue').forEach((formulario) => {
    const casilla = formulario.querySelector('input[type="checkbox"]');
    const fila    = formulario.closest('.fila-puesto');

    casilla.addEventListener('change', async () => {
        const nombre    = formulario.dataset.nombre;
        const pregunta  = casilla.checked
            ? `¿${nombre} se fue del retiro? Su silla queda vacía y no se le da a nadie más.`
            : `¿${nombre} volvió? Sale otra vez en la lista.`;

        if (! confirm(pregunta)) {
            casilla.checked = ! casilla.checked;
            return;
        }

        casilla.disabled = true;
        const [ok, datos] = await enviar(formulario);
        casilla.disabled = false;

        if (! ok) {
            casilla.checked = ! casilla.checked;
            mensajeFila(fila, datos.message || 'No se pudo guardar.', 'error');
            return;
        }

        fila.dataset.seFue = datos.seFue ? '1' : '0';
        fila.classList.toggle('fila-se-fue', datos.seFue);
        mensajeFila(fila, datos.seFue ? 'Se fue' : 'Volvió ✓', datos.seFue ? 'error' : 'ok');
        recontar();
    });
});

// ============ BUSCAR Y FILTRAR INVITADOS ============

//  Filtra la tabla mientras se escribe (nombre, apellidos, documento y
// padrino) y según el botón elegido: todos, los que faltan por llegar
// (sin mesa y silla) o los que se fueron.
//  «César», «CESAR» y «cesar» son lo mismo al buscar: sin tildes y en minúscula
const sinTildes = (texto) => String(texto).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();


const buscarPuesto = document.getElementById('buscar-puesto');
let filtro         = 'todos';



function pasaFiltro(fila) {
    if (filtro === 'faltan') {
        return fila.dataset.puesto === '0' && fila.dataset.seFue !== '1';
    }

    if (filtro === 'se-fueron') {
        return fila.dataset.seFue === '1';
    }

    return true;
}



function filtrarInvitados() {
    const texto = sinTildes(buscarPuesto.value.trim());
    let visibles = 0;

    document.querySelectorAll('.fila-puesto').forEach((fila) => {
        // Una fila con cambios sin guardar no se esconde
        const mostrar = fila.classList.contains('fila-cambiada') || (sinTildes(fila.dataset.buscar).includes(texto) && pasaFiltro(fila));
        fila.hidden = ! mostrar;

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


// ============ NO PERDER CAMBIOS ============

//  Si quedó una fila sin guardar, el navegador avisa antes de salir
window.addEventListener('beforeunload', (evento) => {
    if (document.querySelector('.fila-cambiada')) {
        evento.preventDefault();
        evento.returnValue = '';
    }
});
