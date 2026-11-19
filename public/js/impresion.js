// ============ IMPRIMIR ============

//  Si a la hoja le falta algo (por ejemplo, la carta sin próximo retiro),
// se pregunta antes de imprimir
document.getElementById('boton-imprimir').addEventListener('click', () => {
    const falta = document.querySelector('[data-confirmar-impresion]');

    if (falta && ! confirm(falta.dataset.confirmarImpresion)) {
        return;
    }

    window.print();
});
