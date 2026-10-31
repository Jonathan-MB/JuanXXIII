// ============ MENSAJES ============

// Los mensajes de éxito se ocultan solos; los de error se quedan
const mensaje = document.getElementById('mensaje');

if (mensaje && !mensaje.classList.contains('mensaje-error')) {
    setTimeout(() => {
        mensaje.classList.add('mensaje-oculto');
    }, 4000);
}
