<?php

namespace App\Support;

//  Las hojas de la página Impresiones, con su nombre y para qué sirven.
// Las usa la página Impresiones y el buscador general.
class CatalogoImpresiones
{
    public const DOCUMENTOS = [
        [
            'ruta'        => 'impresiones.llegada',
            'nombre'      => 'Lista de llegada',
            'cuando'      => 'Viernes · al llegar los invitados',
            'descripcion' => 'En orden alfabético: nombre, documento, edad, padrino y una casilla para marcar quién llegó.',
        ],
        [
            'ruta'        => 'impresiones.llamado',
            'nombre'      => 'Lista de llamado',
            'cuando'      => 'Viernes · al pasar a sala',
            'descripcion' => 'La relación de invitados del rector(a): en orden alfabético y con el nombre grande, para llamar a cada uno a sala y marcar quién entró.',
        ],
        [
            'ruta'        => 'impresiones.nombresMesa',
            'nombre'      => 'Hoja 1 · Nombres por mesa',
            'cuando'      => 'Viernes · ya sentados en las mesas',
            'descripcion' => 'En blanco, dos mesas por hoja: cada persona escribe su nombre en la fila de su silla. Con ella se revisa que los nombres estén bien en la aplicación.',
            'soloGrupo'   => true,
        ],
        [
            'ruta'        => 'impresiones.mesasEnBlanco',
            'nombre'      => 'Hoja 2 · Datos por mesa',
            'cuando'      => 'Viernes o sábado temprano · en las mesas',
            'descripcion' => 'En blanco, una mesa por hoja horizontal: silla, nombre, fecha de nacimiento, celular, ciudad, barrio y dos contactos de emergencia. Se corrige en Llegada con «Editar datos».',
            'soloGrupo'   => true,
        ],
        [
            'ruta'        => 'impresiones.correcciones',
            'nombre'      => 'Hoja 3 · Correcciones',
            'cuando'      => 'Sábado · última revisión de los datos',
            'descripcion' => 'Ya impresa con los datos corregidos, por mesa: silla, nombre, documento, fecha de nacimiento, ciudad, barrio, celular y contactos de emergencia. La última revisión.',
        ],
        [
            'ruta'        => 'impresiones.membretes',
            'nombre'      => 'Membretes oficio (dinámicas)',
            'cuando'      => 'Sábado y domingo · en las dinámicas',
            'descripcion' => 'Hoja oficio con el logo, el número y las fechas del retiro arriba, para las dinámicas. Una por invitado; se entrega con una hoja en blanco más.',
        ],
        [
            'ruta'        => 'impresiones.oraciones',
            'nombre'      => 'Oraciones',
            'cuando'      => 'Viernes · se reparten al empezar',
            'descripcion' => 'Invocación al Espíritu Santo, Padre Nuestro y Credo con el número, el lugar y la fecha del retiro. Una por invitado, dos por hoja carta para recortar.',
        ],
        [
            'ruta'        => 'impresiones.carta',
            'nombre'      => 'Cartas al presbítero',
            'cuando'      => 'Domingo · al cierre del retiro',
            'descripcion' => 'Al final del retiro, una por página para cada persona que llegó y terminó (no a quien se fue). Por hombres y mujeres, en orden de mesa y silla.',
        ],
        [
            'ruta'        => 'impresiones.directorio',
            'nombre'      => 'Directorio',
            'cuando'      => 'Antes del retiro · para el rectorado',
            'descripcion' => 'Rectorado arriba, cada mesa con CT, CA y sus sillas, y las perseverancias al pie.',
        ],
        [
            'ruta'        => 'impresiones.ubicacion',
            'nombre'      => 'Hojas de ubicación',
            'cuando'      => 'Viernes · en cada mesa antes de la llegada',
            'descripcion' => 'Una hoja por mesa con el CA arriba, el CT abajo y las sillas alrededor, con el nombre de cada persona.',
        ],
        [
            'ruta'        => 'impresiones.previstos',
            'nombre'      => 'Entrega de previstos',
            'cuando'      => 'Domingo · al entregar camisetas y previstos',
            'descripcion' => 'Por mesa, con CT y CA: casillas para camiseta (con la talla), crucifijo, camándula, cartas y previstos; falda solo en mujeres. Sin mesas, sale en blanco.',
        ],
        [
            'ruta'        => 'impresiones.camisetas',
            'nombre'      => 'Camisetas por talla',
            'cuando'      => 'Antes del retiro · para Librería',
            'descripcion' => 'Para Librería: cuántas hay de cada talla y la talla de cada persona por mesa.',
        ],
        [
            'ruta'        => 'impresiones.sobres',
            'nombre'      => 'Rótulos para sobres',
            'cuando'      => 'Antes del retiro · para los sobres',
            'descripcion' => 'Diez por hoja, para recortar y pegar en los sobres de «Ayudas de Dios»: nombre, mesa y silla.',
        ],
        [
            'ruta'        => 'impresiones.llamadas',
            'nombre'      => 'Llamadas familiares',
            'cuando'      => 'Sábado · para llamar a las familias',
            'descripcion' => 'Por mesa: cada invitado con sus contactos de emergencia, parentesco, teléfonos y una casilla para marcar la llamada.',
        ],
        [
            'ruta'        => 'impresiones.puertas',
            'nombre'      => 'Listado de invitados para puertas',
            'cuando'      => 'Todo el retiro · en las puertas',
            'descripcion' => 'Hombres y mujeres por separado, por mesa y en orden de silla: nombre, ciudad, barrio y padrino. Letra grande, fácil de leer.',
        ],
        [
            'ruta'        => 'impresiones.familiares',
            'nombre'      => 'Lista para familiares',
            'cuando'      => 'Domingo · cuando llegan las familias',
            'descripcion' => 'Horizontal, por mesa, para llenar a lapicero: si vino la familia y, si el invitado vino con otro invitado, si es hombre o mujer y su mesa y silla.',
        ],
        [
            'ruta'        => 'impresiones.catequistas',
            'nombre'      => 'Catequistas por mesa',
            'cuando'      => 'Antes del retiro · carpetas del rectorado',
            'descripcion' => 'La «distribución en sala de catequistas» de las carpetas: cada mesa con su CT y su CA, ubicadas en U como en la sala. Si aún no hay mesas, salen en blanco.',
        ],
        [
            'ruta'        => 'desarrollo.imprimir',
            'parametros'  => ['formato' => 'charlistas'],
            'nombre'      => 'Lista de charlistas',
            'cuando'      => 'Antes del retiro · carpetas del rectorado',
            'descripcion' => 'Carpetas del rectorado: las charlas de cada día con su hora y el charlista escrito en el Desarrollo del retiro.',
        ],
        [
            'ruta'        => 'desarrollo.imprimir',
            'parametros'  => ['formato' => 'sala'],
            'nombre'      => 'Rotación al Santísimo en sala',
            'cuando'      => 'Durante las charlas · carpeta del rector(a)',
            'descripcion' => 'Carpeta del rector(a), dos copias: franjas de una hora durante las charlas, con espacio para escribir el catequista.',
        ],
        [
            'ruta'        => 'impresiones.carpeta',
            'parametros'  => ['cargo' => 'rector'],
            'nombre'      => 'Carpeta del rector(a) completa',
            'cuando'      => 'Antes del retiro · para armar la carpeta',
            'descripcion' => 'Todo junto en una sola impresión: desarrollo (2), relación de invitados, catequistas por mesa, rotación en sala (2), charlistas, arqueo de la ofrenda e informe del rector(a).',
        ],
        [
            'ruta'        => 'impresiones.carpeta',
            'parametros'  => ['cargo' => 'vicerrector'],
            'nombre'      => 'Carpeta del vicerrector(a) completa',
            'cuando'      => 'Antes del retiro · para armar la carpeta',
            'descripcion' => 'Todo junto en una sola impresión: desarrollo, problemáticas, evaluación de equipos (solo uno de los dos vicerrectores) e informe del vicerrector(a).',
        ],
        [
            'ruta'        => 'impresiones.carpeta',
            'parametros'  => ['cargo' => 'dirigente'],
            'nombre'      => 'Carpeta del dirigente responsable completa',
            'cuando'      => 'Antes del retiro · para armar la carpeta',
            'descripcion' => 'Todo junto en una sola impresión: desarrollo, catequistas por mesa, evaluación de charlas, charlistas e informe del dirigente responsable.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'dirigente'],
            'nombre'      => 'Informe del dirigente responsable',
            'cuando'      => 'Todo el retiro · se entrega al final',
            'descripcion' => 'Carpeta del dirigente: cada charla del desarrollo con renglones para comentarla, la clausura y las observaciones.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'rector'],
            'nombre'      => 'Informe del rector(a)',
            'cuando'      => 'Todo el retiro · se entrega al final',
            'descripcion' => 'Catequistas que no asistieron, la llegada de cada CT, CA y CR por mesa, colaboraciones, sacerdotes y lo que se adjunta.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'vicerrector'],
            'nombre'      => 'Informe del vicerrector(a)',
            'cuando'      => 'Todo el retiro · se entrega al final',
            'descripcion' => 'Quienes no asistieron a sus equipos, la llegada del responsable de cada equipo y el accionar de los equipos (precios y servicio).',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'equipos'],
            'nombre'      => 'Evaluación de equipos',
            'cuando'      => 'Todo el retiro · se entrega al final',
            'descripcion' => 'Carpeta del vicerrector(a): una tabla por equipo con el nombre de cada dirigente, su hora de llegada y observaciones.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'problematicas'],
            'nombre'      => 'Problemáticas',
            'cuando'      => 'Todo el retiro · carpeta del vicerrector(a)',
            'descripcion' => 'Hoja numerada del 1 al 20 para anotar la problemática de cada invitado y su mesa.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'ofrenda'],
            'nombre'      => 'Acta de entrega de la ofrenda',
            'cuando'      => 'Domingo · al entregar la ofrenda',
            'descripcion' => 'Arqueo de billetes y monedas con el rectorado ya escrito, para entregar la ofrenda al tesorero(a) con las firmas.',
        ],
        [
            'ruta'        => 'impresiones.informe',
            'parametros'  => ['formato' => 'charlas'],
            'nombre'      => 'Evaluación de charlas',
            'cuando'      => 'En cada charla · se entrega al final',
            'descripcion' => 'Una hoja por charla con el charlista ya escrito: horario, reto, previsto, doctrina, los temas que debe tocar para marcar SI / NO y la calificación.',
        ],
    ];
}
