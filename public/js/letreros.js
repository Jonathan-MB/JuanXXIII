// ============ MINIATURAS ============

//  Cada vista previa es la hoja de tamaño real (996 px de ancho) metida
// en un recuadro: se escala para que llene el ancho del recuadro.
const ANCHO_HOJA = 996;



function escalarMiniaturas() {
    document.querySelectorAll('.miniatura-letrero').forEach((recuadro) => {
        const escala = recuadro.clientWidth / ANCHO_HOJA;

        recuadro.querySelector('iframe').style.transform = `scale(${escala})`;
    });
}



escalarMiniaturas();
window.addEventListener('resize', escalarMiniaturas);
