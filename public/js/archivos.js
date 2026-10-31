// ============ ARCHIVOS ELEGIDOS PARA SUBIR ============

const LIMITE_ARCHIVOS = 20;
const LIMITE_MB       = 20;

const entradaArchivos = document.getElementById('archivosNuevos');
const listaElegidos   = document.getElementById('lista-elegidos');

//  Muestra qué se va a subir antes de enviar. Se usa textContent, nunca
// innerHTML, porque el nombre del archivo lo escribió otra persona.
entradaArchivos.addEventListener('change', () => {
    listaElegidos.replaceChildren();

    [...entradaArchivos.files].forEach((archivo) => {
        const elemento = document.createElement('li');
        const megas    = archivo.size / 1024 / 1024;
        elemento.textContent = archivo.name + ' · ' + (megas >= 1 ? megas.toFixed(1) + ' MB' : Math.max(1, Math.round(archivo.size / 1024)) + ' KB');
        if (megas > LIMITE_MB) {
            elemento.classList.add('elegido-grande');
        }
        listaElegidos.appendChild(elemento);
    });
});

document.getElementById('formulario-subir').addEventListener('submit', (evento) => {
    const archivos = [...entradaArchivos.files];

    if (archivos.length > LIMITE_ARCHIVOS) {
        evento.preventDefault();
        alert('Se pueden subir máximo ' + LIMITE_ARCHIVOS + ' archivos a la vez.');
        return;
    }

    if (archivos.some((archivo) => archivo.size > LIMITE_MB * 1024 * 1024)) {
        evento.preventDefault();
        alert('Hay archivos de más de ' + LIMITE_MB + ' MB (marcados en rojo). Quítalos e intenta de nuevo.');
        return;
    }

    const boton = document.getElementById('boton-subir');
    boton.disabled    = true;
    boton.textContent = 'Subiendo…';
});


// ============ BUSCAR ============

const buscarArchivo = document.getElementById('buscar-archivo');

if (buscarArchivo) {
    buscarArchivo.addEventListener('input', () => {
        const texto = buscarArchivo.value.trim().toLowerCase();
        let visibles = 0;

        document.querySelectorAll('.fila-archivo').forEach((fila) => {
            const coincide = fila.dataset.buscar.includes(texto);
            fila.hidden = ! coincide;
            if (coincide) {
                visibles++;
            }
        });

        document.getElementById('archivos-sin-resultados').hidden = visibles > 0;
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
