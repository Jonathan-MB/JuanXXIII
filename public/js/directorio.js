// ============ DIRECTORIO EN UNA SOLA HOJA ============

//  Cada grupo debe salir en una sola hoja, a una cara, con cada mesa a
// todo el ancho. Primero se intenta en carta y, solo si no cabe, en
// oficio; en cada papel se baja la letra hasta que todo quepa. Por debajo
// de LETRA_MINIMA ya no se lee bien.
const LETRA_MAXIMA = 10;
const LETRA_MINIMA = 5.5;
const PASO         = 0.25;

// Alto útil de la hoja en cm: el papel menos 1 cm de margen arriba y abajo.
// El oficio de Colombia mide 21,6 × 33 cm (no el legal de 35,6 cm).
const ALTO_UTIL = {
    carta:  27.94 - 2,
    oficio: 33.02 - 2,
};

const PAPELES = ['carta', 'oficio'];

const PIXELES_POR_CM = 96 / 2.54;



function aplicar(hoja, papel, letra) {
    hoja.classList.toggle('papel-carta', papel === 'carta');
    hoja.classList.toggle('papel-oficio', papel === 'oficio');
    hoja.querySelector('.contenido-directorio').style.fontSize = letra + 'pt';
}



//  Medio centímetro de holgura: la impresora no siempre respeta al
// milímetro lo que se ve en pantalla.
function cabe(hoja, papel) {
    const alto = hoja.querySelector('.contenido-directorio').scrollHeight;

    return alto <= (ALTO_UTIL[papel] - 0.5) * PIXELES_POR_CM;
}



function acomodar(hoja) {
    for (const papel of PAPELES) {
        for (let letra = LETRA_MAXIMA; letra >= LETRA_MINIMA; letra -= PASO) {
            aplicar(hoja, papel, letra);

            if (cabe(hoja, papel)) {
                return { papel, letra, cabe: true };
            }
        }
    }

    // Ni en oficio: se deja lo más pequeño y se avisa
    aplicar(hoja, 'oficio', LETRA_MINIMA);

    return { papel: 'oficio', letra: LETRA_MINIMA, cabe: false };
}



function avisar(resultados) {
    const aviso  = document.getElementById('aviso-papel');
    const lineas = resultados.map(({ grupo, papel, letra, cabe }) => {
        if (!cabe) {
            return `<strong>${grupo}:</strong> no cabe en una sola hoja ni en oficio. Revisa que no sobren mesas o personas.`;
        }

        return papel === 'carta'
            ? `<strong>${grupo}:</strong> cabe en una hoja <strong>carta</strong>, a una cara (letra de ${letra} pt).`
            : `<strong>${grupo}:</strong> necesita hoja <strong>OFICIO</strong>, a una cara (letra de ${letra} pt). Pon papel oficio en la impresora.`;
    });

    aviso.innerHTML = lineas.map((linea) => `<p>${linea}</p>`).join('');
}



//  Se espera a que carguen el logo y el QR: cambian el alto de la hoja
window.addEventListener('load', () => {
    const resultados = [...document.querySelectorAll('.hoja-directorio')].map((hoja) => ({
        grupo: hoja.dataset.grupo,
        ...acomodar(hoja),
    }));

    if (resultados.length > 0) {
        avisar(resultados);
    } else {
        document.getElementById('aviso-papel').remove();
    }
});
