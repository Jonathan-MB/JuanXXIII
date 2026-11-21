// ============ BUSCAR Y FILTRAR ============

const buscador     = document.getElementById('buscar-articulo');
const tarjetas     = document.querySelectorAll('.tarjeta-articulo');
const sinResultado = document.getElementById('sin-resultados');
let filtroActual   = 'todos';

//  «César», «CESAR» y «cesar» son lo mismo al buscar: sin tildes y en minúscula
const sinTildes = (texto) => String(texto).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();


function aplicarFiltros() {
    const texto  = buscador ? sinTildes(buscador.value.trim()) : '';
    let visibles = 0;

    tarjetas.forEach((tarjeta) => {
        const coincide = sinTildes(tarjeta.dataset.nombre).includes(texto)
            && (filtroActual === 'todos' || tarjeta.dataset.comprar === '1');

        tarjeta.hidden = ! coincide;
        if (coincide) {
            visibles++;
        }
    });

    if (sinResultado) {
        sinResultado.hidden = visibles > 0;
    }
}

if (buscador) {
    buscador.addEventListener('input', aplicarFiltros);
}

document.querySelectorAll('.opcion-filtro').forEach((boton) => {
    boton.addEventListener('click', () => {
        filtroActual = boton.dataset.filtro;
        document.querySelectorAll('.opcion-filtro').forEach((otro) => otro.classList.toggle('opcion-activa', otro === boton));
        aplicarFiltros();
    });
});


// ============ SALIDA Y ENTRADA ============

//  Al abrir "Salida" se cierra "Entrada" de la misma tarjeta (y al revés),
// y el cursor queda listo en la cantidad.
document.querySelectorAll('.movimiento').forEach((detalle) => {
    detalle.addEventListener('toggle', () => {
        if (! detalle.open) {
            return;
        }
        detalle.parentElement.querySelectorAll('.movimiento').forEach((otro) => {
            if (otro !== detalle) {
                otro.open = false;
            }
        });
        const cantidad = detalle.querySelector('input[name="cantidad"]');
        cantidad.focus();
        cantidad.select();
    });
});


// Evita que un doble clic registre dos veces el mismo movimiento
document.querySelectorAll('.formulario-movimiento').forEach((formulario) => {
    formulario.addEventListener('submit', () => {
        formulario.querySelector('button[type="submit"]').disabled = true;
    });
});
