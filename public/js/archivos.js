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


// ============ VISTA PREVIA ============

//  "Ver" abre el documento dentro de la página. Los PDF se dibujan página
// por página con PDF.js (de Mozilla, guardado en la app) porque muchos
// navegadores de celular no muestran un PDF dentro de la página: lo
// descargan. Si el navegador no tiene <dialog>, el enlace sigue
// funcionando y lo abre en otra pestaña.
const visor = document.getElementById('visor-archivo');

if (visor && typeof visor.showModal === 'function') {
    const paginas = document.getElementById('visor-paginas');
    let pdfjsListo = null;
    let abierto    = 0; // para ignorar un PDF que termina de cargar después de cerrar o cambiar

    // PDF.js pesa: se carga solo la primera vez que se abre un documento
    const cargarPdfjs = () => {
        pdfjsListo ??= new Promise((resolver, rechazar) => {
            const script = document.createElement('script');
            script.src     = paginas.dataset.pdfjs;
            script.onload  = () => {
                window.pdfjsLib.GlobalWorkerOptions.workerSrc = paginas.dataset.worker;
                resolver(window.pdfjsLib);
            };
            script.onerror = rechazar;
            document.head.appendChild(script);
        });
        return pdfjsListo;
    };

    const aviso = (texto) => {
        const parrafo = document.createElement('p');
        parrafo.className   = 'visor-aviso';
        parrafo.textContent = texto;
        paginas.replaceChildren(parrafo);
    };

    const mostrarPdf = async (url, numero) => {
        aviso('Cargando…');
        try {
            const pdfjsLib = await cargarPdfjs();
            const documento = await pdfjsLib.getDocument({ url, withCredentials: true }).promise;
            if (numero !== abierto) {
                return;
            }
            paginas.replaceChildren();

            // Ancho de la página igual al del visor; nítido en pantallas de alta densidad
            const ancho    = Math.min(paginas.clientWidth - 24, 900);
            const densidad = window.devicePixelRatio || 1;

            for (let n = 1; n <= documento.numPages; n++) {
                const pagina = await documento.getPage(n);
                if (numero !== abierto) {
                    return;
                }
                const escala = ancho / pagina.getViewport({ scale: 1 }).width;
                const vista  = pagina.getViewport({ scale: escala * densidad });
                const lienzo = document.createElement('canvas');
                lienzo.className    = 'visor-pagina';
                lienzo.width        = vista.width;
                lienzo.height       = vista.height;
                lienzo.style.width  = (vista.width / densidad) + 'px';
                lienzo.setAttribute('aria-label', 'Página ' + n + ' de ' + documento.numPages);
                paginas.appendChild(lienzo);
                await pagina.render({ canvasContext: lienzo.getContext('2d'), viewport: vista }).promise;
            }
        } catch (error) {
            if (numero === abierto) {
                aviso('No se pudo mostrar la vista previa. Usa «Abrir para imprimir» o «Descargar».');
            }
        }
    };

    document.querySelectorAll('.ver-archivo').forEach((enlace) => {
        enlace.addEventListener('click', (evento) => {
            evento.preventDefault();
            abierto++;
            document.getElementById('visor-titulo').textContent = enlace.dataset.titulo;
            document.getElementById('visor-pestana').href = enlace.href;
            visor.showModal();

            if (enlace.dataset.tipo === 'imagen') {
                const imagen = document.createElement('img');
                imagen.className = 'visor-pagina';
                imagen.src       = enlace.href;
                imagen.alt       = enlace.dataset.titulo;
                paginas.replaceChildren(imagen);
            } else {
                mostrarPdf(enlace.href, abierto);
            }
        });
    });

    document.getElementById('visor-cerrar').addEventListener('click', () => visor.close());

    // Al cerrar se vacía el visor para liberar la memoria de las páginas
    visor.addEventListener('close', () => {
        abierto++;
        paginas.replaceChildren();
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
