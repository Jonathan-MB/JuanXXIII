// ============ MENSAJES ============

// Los mensajes de éxito se ocultan solos; los de error se quedan
const mensaje = document.getElementById('mensaje');

if (mensaje && !mensaje.classList.contains('mensaje-error')) {
    setTimeout(() => {
        mensaje.classList.add('mensaje-oculto');
    }, 4000);
}



// ============ SOLO NÚMEROS ============

//  Cédulas, celulares y teléfonos: lo que no sea un dígito se borra al
// escribir o pegar. Se escucha en el documento para que también sirva
// en las filas que se agregan después (contactos del retiro).
document.addEventListener('input', (evento) => {
    const campo = evento.target;

    if (!campo.matches('[data-solo-numeros]')) {
        return;
    }

    const limpio = campo.value.replace(/\D/g, '');

    if (limpio !== campo.value) {
        campo.value = limpio;
    }
});
