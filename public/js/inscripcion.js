// ============ REFERENCIAS ============

const formulario     = document.getElementById('formulario-inscripcion');
const pasos          = formulario.querySelectorAll('.paso-inscripcion');
const botonAnterior  = document.getElementById('boton-anterior');
const botonSiguiente = document.getElementById('boton-siguiente');
const botonEnviar    = document.getElementById('boton-enviar');
const textoPaso      = document.getElementById('texto-paso');

const nombresPasos = ['Datos personales', 'Salud y contactos', 'Pago y padrino'];
const EDAD_MINIMA  = 16;
const PESO_MAXIMO  = 5 * 1024 * 1024;

let pasoActual = 1;


// ============ NAVEGACIÓN ============

function mostrarPaso(numero) {
    pasoActual = numero;

    pasos.forEach((paso) => {
        paso.hidden = Number(paso.dataset.paso) !== numero;
    });

    formulario.querySelectorAll('.barra-paso').forEach((barra) => {
        barra.classList.toggle('barra-activa', Number(barra.dataset.barra) <= numero);
    });

    textoPaso.textContent = 'Paso ' + numero + ' de 3 · ' + nombresPasos[numero - 1];
    botonAnterior.hidden  = numero === 1;
    botonSiguiente.hidden = numero === 3;
    botonEnviar.hidden    = numero !== 3;

    formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
}


//  Revisa los campos visibles del paso con las reglas del navegador
// (required, email, maxlength) y muestra el primer error que encuentre.
// Se saltan los bloques ocultos DENTRO del paso (padrino, medicamentos),
// pero no el paso mismo, que está oculto cuando no es el actual.
function pasoValido(numero, avisar = true) {
    const paso   = formulario.querySelector('.paso-inscripcion[data-paso="' + numero + '"]');
    const campos = paso.querySelectorAll('input, select, textarea');

    for (const campo of campos) {
        const oculto = campo.closest('[hidden]');
        if (oculto && oculto !== paso) {
            continue;
        }
        if (! campo.checkValidity()) {
            if (avisar) {
                campo.reportValidity();
            }
            return false;
        }
    }

    return true;
}


function edadAlIniciar(fechaNacimiento) {
    const nacimiento = new Date(fechaNacimiento + 'T00:00:00');
    const inicio     = new Date(retiroInscripcion.fechaInicio + 'T00:00:00');
    let edad         = inicio.getFullYear() - nacimiento.getFullYear();

    if (inicio < new Date(inicio.getFullYear(), nacimiento.getMonth(), nacimiento.getDate())) {
        edad--;
    }

    return edad;
}


//  Se pregunta al servidor antes de seguir, para que nadie llene todo
// el formulario y al final descubra que su cédula no puede inscribirse.
async function cedulaPermitida() {
    const cedula = document.getElementById('cedula').value.trim();

    try {
        const respuesta = await fetch(rutaVerificarCedula + '/' + encodeURIComponent(cedula), {
            headers: { 'Accept': 'application/json' },
        });

        if (! respuesta.ok) {
            // Si el servidor no responde, el servidor revalida al enviar
            return true;
        }

        const datos = await respuesta.json();

        if (! datos.puede) {
            alert(datos.mensaje);
            return false;
        }
    } catch (error) {
        return true;
    }

    return true;
}


botonSiguiente.addEventListener('click', async () => {
    if (! pasoValido(pasoActual)) {
        return;
    }

    if (pasoActual === 1) {
        const fecha = document.getElementById('fechaNacimiento').value;

        if (edadAlIniciar(fecha) < EDAD_MINIMA) {
            alert('El retiro es para mayores de ' + EDAD_MINIMA + ' años.');
            return;
        }

        botonSiguiente.disabled = true;
        const permitida = await cedulaPermitida();
        botonSiguiente.disabled = false;

        if (! permitida) {
            return;
        }
    }

    mostrarPaso(pasoActual + 1);
});


botonAnterior.addEventListener('click', () => {
    mostrarPaso(pasoActual - 1);
});


// ============ AVISO DEL RETIRO SEGÚN EL SEXO ============

const avisoRetiro = document.getElementById('aviso-retiro');

function actualizarAviso() {
    const elegido = formulario.querySelector('input[name="sexo"]:checked');

    if (! elegido) {
        avisoRetiro.hidden = true;
        return;
    }

    const esMujer = elegido.value === 'Femenino';
    avisoRetiro.textContent = 'Te vas a inscribir en el Retiro N.º '
        + (esMujer ? retiroInscripcion.numeroMujeres + ' de mujeres.' : retiroInscripcion.numeroHombres + ' de hombres.');
    avisoRetiro.hidden = false;
}

formulario.querySelectorAll('input[name="sexo"]').forEach((opcion) => {
    opcion.addEventListener('change', actualizarAviso);
});


// ============ SACRAMENTOS ============

const sacramentos = formulario.querySelectorAll('.casilla-sacramento');

document.getElementById('marcar-todos').addEventListener('click', () => {
    sacramentos.forEach((casilla) => { casilla.checked = true; });
});

document.getElementById('marcar-ninguno').addEventListener('click', () => {
    sacramentos.forEach((casilla) => { casilla.checked = false; });
});


// ============ CAMPOS QUE APARECEN SEGÚN LA RESPUESTA ============

//  Muestra u oculta un bloque y hace obligatorios sus campos solo
// cuando está visible, para que no bloqueen al estar ocultos.
function alternarBloque(nombreRadio, valorQueMuestra, idBloque, idsObligatorios) {
    const elegido = formulario.querySelector('input[name="' + nombreRadio + '"]:checked');
    const mostrar = elegido !== null && elegido.value === valorQueMuestra;
    const bloque  = document.getElementById(idBloque);

    bloque.hidden = ! mostrar;
    idsObligatorios.forEach((id) => {
        document.getElementById(id).required = mostrar;
    });
}

function actualizarBloques() {
    alternarBloque('tomaMedicamentos', '1', 'bloque-medicamentos', ['medicamentos']);
    alternarBloque('tienePadrino', '1', 'bloque-padrino', ['padrinoNombre']);
    alternarBloque('formaPago', 'Otro', 'formaPagoOtro', ['formaPagoOtro']);
}

formulario.querySelectorAll('input[name="tomaMedicamentos"], input[name="tienePadrino"], input[name="formaPago"]').forEach((opcion) => {
    opcion.addEventListener('change', actualizarBloques);
});


// ============ COMPROBANTE ============

document.getElementById('comprobante').addEventListener('change', (evento) => {
    const archivo = evento.target.files[0];

    if (archivo && archivo.size > PESO_MAXIMO) {
        alert('El comprobante pesa más de 5 MB. Toma una foto más liviana o envíalo después por WhatsApp.');
        evento.target.value = '';
    }
});


// ============ ENVÍO ============

formulario.addEventListener('submit', (evento) => {
    for (const numero of [1, 2, 3]) {
        if (! pasoValido(numero, false)) {
            evento.preventDefault();
            // Primero se muestra el paso, porque un campo oculto no puede señalar su error
            mostrarPaso(numero);
            pasoValido(numero);
            return;
        }
    }

    // Evita que un doble clic mande dos inscripciones
    botonEnviar.disabled    = true;
    botonEnviar.textContent = 'Enviando…';
});


// ============ AL CARGAR ============

//  Si el servidor devolvió errores, se abre el paso del primer campo
// con problema en vez de dejar a la persona en el paso 1.
const pasoDeCampo = {
    sexo: 1, nombres: 1, apellidos: 1, cedula: 1, fecha_nacimiento: 1, correo: 1, celular: 1, direccion: 1, barrio: 1, ciudad: 1,
    toma_medicamentos: 2, medicamentos: 2, eps: 2, talla_camiseta: 2, contactos_emergencia: 2,
    forma_pago: 3, comprobante: 3, tiene_padrino: 3, padrino_nombre: 3, acepta_datos: 3, declara_requisitos: 3,
};

actualizarAviso();
actualizarBloques();

if (erroresServidor.length > 0) {
    const primero = erroresServidor[0].split('.')[0];
    mostrarPaso(pasoDeCampo[primero] || 1);
}
