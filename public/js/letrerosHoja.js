// ============ TÍTULOS QUE CABEN ============

//  El título de cada letrero va lo más grande posible, pero sin partir
// una palabra ("SILENC / IO") ni salirse de la hoja: se baja la letra de
// a poco hasta que quepa. LETRA_MINIMA evita un ciclo sin fin.
const LETRA_MINIMA = 24;



function cabeTitulo(titulo, hoja) {
    return titulo.scrollWidth <= titulo.clientWidth + 1
        && hoja.scrollHeight <= hoja.clientHeight + 1;
}



function ajustarTitulo(titulo) {
    const hoja = titulo.closest('.letrero-pared');
    let letra  = parseFloat(getComputedStyle(titulo).fontSize);

    // Sin partir palabras: si una no cabe, se nota en el ancho y se achica
    titulo.style.overflowWrap = 'normal';

    while (!cabeTitulo(titulo, hoja) && letra > LETRA_MINIMA) {
        letra -= 2;
        titulo.style.fontSize = letra + 'px';
    }
}



//  Se espera a la letra Montserrat: con otra letra los anchos cambian
function ajustarTodos() {
    document.querySelectorAll('.letrero-pared .titulo-letrero, .letrero-pared .numero-cuenta').forEach(ajustarTitulo);
}

document.fonts.ready.then(ajustarTodos);

// Y justo antes de imprimir, por si se imprime antes de que cargue la letra
window.addEventListener('beforeprint', ajustarTodos);
