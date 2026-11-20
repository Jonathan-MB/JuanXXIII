// ============ BUSCADOR GENERAL ============

//  Los resultados cambian mientras se escribe, sin recargar la página.
// Si algo falla, el botón «Buscar» sigue funcionando como siempre.
const cajaBuscador       = document.getElementById('caja-buscador');
const resultadosBuscador = document.getElementById('resultados-buscador');
const formularioBuscador = document.getElementById('formulario-buscador');

if (cajaBuscador && resultadosBuscador && formularioBuscador) {
    let espera   = null;
    let pedido   = null;
    let ultimo   = cajaBuscador.value.trim();

    const buscar = async () => {
        const texto = cajaBuscador.value.trim();

        if (texto === ultimo) {
            return;
        }
        ultimo = texto;

        pedido?.abort();
        pedido = new AbortController();
        resultadosBuscador.classList.add('buscando');

        const direccion = new URL(formularioBuscador.action);
        if (texto !== '') {
            direccion.searchParams.set('q', texto);
        }

        try {
            const pedidoUrl = new URL(direccion);
            pedidoUrl.searchParams.set('parcial', '1');
            const respuesta = await fetch(pedidoUrl, { signal: pedido.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });

            if (respuesta.ok) {
                resultadosBuscador.innerHTML = await respuesta.text();
                history.replaceState(null, '', direccion);
            }
        } catch (error) {
            // Se canceló porque siguió escribiendo: no pasa nada
        } finally {
            resultadosBuscador.classList.remove('buscando');
        }
    };

    cajaBuscador.addEventListener('input', () => {
        clearTimeout(espera);
        espera = setTimeout(buscar, 300);
    });

    // Con el cursor al final, para seguir escribiendo
    const largo = cajaBuscador.value.length;
    cajaBuscador.setSelectionRange(largo, largo);
}
