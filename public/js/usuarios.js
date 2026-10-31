// ============ VALIDACIÓN ============

document.getElementById('formulario-cuenta').addEventListener('submit', (evento) => {
    const contrasena   = document.getElementById('contrasena').value;
    const confirmacion = document.getElementById('contrasenaConfirmacion').value;

    if (contrasena !== confirmacion) {
        evento.preventDefault();
        alert('Las dos contraseñas no coinciden.');
    }
});
