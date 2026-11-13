// ============ CARRUSEL DE PERSEVERANCIAS ============

const carrusel = document.getElementById('carrusel-perseverancias');

if (carrusel) {
    //  Infinito: las diapositivas se repiten una vez detrás de las
    // originales. Cuando se pasa a la copia, se salta sin animación a la
    // original igual, así nunca se ve que vuelve al comienzo.
    const originales = [...carrusel.querySelectorAll('.diapositiva')];

    originales.forEach((diapositiva) => {
        const copia = diapositiva.cloneNode(true);
        copia.setAttribute('aria-hidden', 'true');
        copia.querySelectorAll('a').forEach((enlace) => enlace.setAttribute('tabindex', '-1'));
        carrusel.appendChild(copia);
    });

    const paso       = () => originales[0].offsetWidth + 14;
    const anchoJuego = () => paso() * originales.length;

    // Salto instantáneo (sin animación ni imán) a la posición equivalente
    const saltar = (izquierda) => {
        carrusel.style.scrollSnapType = 'none';
        carrusel.scrollTo({ left: izquierda, behavior: 'instant' });
        carrusel.style.scrollSnapType = '';
    };

    //  Se lleva la cuenta de en qué diapositiva va: así no depende de que
    // el navegador avise que terminó de moverse
    let ajuste;

    const mover = (direccion) => {
        const total = originales.length;
        let indice  = Math.round(carrusel.scrollLeft / paso());

        // Hacia atrás desde la primera: primero a su copia, luego retrocede
        if (direccion < 0 && indice === 0) {
            indice = total;
            saltar(indice * paso());
        }

        indice += direccion;
        carrusel.scrollTo({ left: indice * paso(), behavior: 'smooth' });

        // Cuando termina la animación, si quedó en las copias, a la original
        clearTimeout(ajuste);
        ajuste = setTimeout(() => {
            if (indice >= total) {
                saltar((indice - total) * paso());
            }
        }, 700);
    };

    //  Si alguien lo arrastra con el dedo hasta las copias, también vuelve
    let espera;
    carrusel.addEventListener('scroll', () => {
        clearTimeout(espera);
        espera = setTimeout(() => {
            if (carrusel.scrollLeft >= anchoJuego() - 2) {
                saltar(carrusel.scrollLeft - anchoJuego());
            }
        }, 200);
    }, { passive: true });

    document.querySelectorAll('.boton-carrusel').forEach((boton) => {
        boton.addEventListener('click', () => mover(Number(boton.dataset.mover)));
    });

    //  Pasa solo cada 4 segundos, siempre hacia adelante. Se detiene
    // mientras el mouse o el dedo están encima y sigue después.
    let quieto = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let soltar;

    ['mouseenter', 'touchstart', 'focusin'].forEach((evento) => carrusel.addEventListener(evento, () => { quieto = true; clearTimeout(soltar); }, { passive: true }));
    ['mouseleave', 'touchend', 'focusout'].forEach((evento) => carrusel.addEventListener(evento, () => { soltar = setTimeout(() => { quieto = false; }, 3000); }, { passive: true }));

    setInterval(() => {
        if (! quieto && ! document.hidden) {
            mover(1);
        }
    }, 4000);
}


// ============ MAPA: TODAS LAS PERSEVERANCIAS CON SU PUNTO ============

const contenedorMapa = document.getElementById('mapa-perseverancias');

//  Para escribir textos de la base dentro del globo sin que se lean como HTML
const sinHtml = (texto) => String(texto ?? '').replace(/[&<>"']/g, (letra) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[letra]));

if (contenedorMapa && window.L) {
    const puntos = JSON.parse(contenedorMapa.dataset.puntos || '[]');
    const mapa   = L.map(contenedorMapa, { scrollWheelZoom: false }).setView([3.43, -76.52], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(mapa);

    const marcadores = {};

    puntos.forEach((punto, indice) => {
        //  Pin dorado con el número de la lista, en vez de la imagen de Leaflet
        const icono = L.divIcon({
            className: 'pin-perseverancia',
            html: '<span>' + (indice + 1) + '</span>',
            iconSize: [30, 30],
            iconAnchor: [15, 30],
            popupAnchor: [0, -28],
        });

        const globo = '<p class="globo-titulo">' + sinHtml(punto.titulo) + '</p>'
            + '<p class="globo-parroquia">' + sinHtml(punto.parroquia) + '</p>'
            + '<p class="globo-dia">' + sinHtml(punto.dia) + '</p>'
            + '<ul class="globo-actividades">' + punto.actividades.map((actividad) => '<li>' + sinHtml(actividad) + '</li>').join('') + '</ul>'
            + '<p class="globo-direccion">' + sinHtml(punto.direccion) + '</p>'
            + '<a class="globo-enlace" href="' + sinHtml(punto.llegar) + '" target="_blank" rel="noopener">Cómo llegar →</a>';

        marcadores[punto.id] = L.marker([punto.latitud, punto.longitud], { icon: icono, title: punto.titulo })
            .addTo(mapa)
            .bindPopup(globo)
            .on('click', () => marcarEnLista(punto.id));
    });

    // Que se vean todos los puntos de entrada
    if (puntos.length > 1) {
        mapa.fitBounds(puntos.map((punto) => [punto.latitud, punto.longitud]), { padding: [30, 30] });
    } else if (puntos.length === 1) {
        mapa.setView([puntos[0].latitud, puntos[0].longitud], 15);
    }

    const marcarEnLista = (id) => {
        document.querySelectorAll('.lugar-mapa').forEach((lugar) => lugar.classList.toggle('lugar-activo', lugar.dataset.lugar === String(id)));
    };

    //  Al tocar un nombre de la lista, el mapa va a su punto y abre el globo
    document.querySelectorAll('.boton-lugar').forEach((boton) => {
        boton.addEventListener('click', () => {
            const marcador = marcadores[boton.dataset.punto];

            if (! marcador) {
                return;
            }

            mapa.setView(marcador.getLatLng(), 16);
            marcador.openPopup();
            marcarEnLista(boton.dataset.punto);
        });
    });
}
