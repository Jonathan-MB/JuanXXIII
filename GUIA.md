# Guía rápida para Secretaría

Cómo se usan las partes de la aplicación que más se manejan durante el retiro. Para instalarla, respaldarla o probarla, ver [README.md](README.md).

## Llegada y puestos

Menú **Llegada**, con una pestaña para Hombres y otra para Mujeres.

- **Cada persona es una fila que se corrige ahí mismo.** Se escribe directo en la casilla:
  - nombres y apellidos;
  - documento y celular;
  - fecha de nacimiento y talla (con la edad al lado);
  - ciudad y barrio;
  - los dos contactos de emergencia;
  - mesa y silla.
- Al cambiar algo, la fila se pone **amarilla**. Se guarda con **Guardar** o con Enter, sin recargar la página.
- **Tener mesa y silla es haber llegado.** No hay botón «Llegó»: al guardar la mesa y la silla, la persona cuenta como llegada; si se le borran, vuelve a «faltan por llegar».
- **Se fue**: la casilla de cada fila marca que la persona abandonó el retiro. La fila queda gris y el nombre tachado. Conserva su silla, que queda vacía y no se le da a nadie más. Se puede desmarcar si volvió.
- Arriba se ve cuántos **llegaron**, cuántos **faltan por llegar** y cuántos **se fueron**. Los filtros muestran todos, solo los que faltan o solo los que se fueron.
- **Más datos** abre el formulario completo: correo, dirección, EPS, medicamentos, sacramentos, forma de pago, padrino. Al guardar, vuelve a Llegada.
- En pantallas de portátil, cada persona se ve como una tarjeta en vez de la tabla ancha, para no tener que achicar la pantalla.

### Agregar a quien llega sin inscribirse

Botón **«+ Agregar persona que llegó sin inscribirse»**, encima de la lista.

- Solo son obligatorios los **nombres** y los **apellidos**. Lo demás puede quedar en blanco: documento, fecha de nacimiento, celular, ciudad, barrio y talla.
- Lo que falte se completa después en su fila o en «Más datos», con las hojas de verificación. En la ficha aparece como «Pendiente».
- Si el documento ya está en la lista, avisa en vez de repetir a la persona. Si esa cédula ya vivió el retiro, no la deja agregar.
- **Esto es solo para Secretaría.** La inscripción pública de la página sigue pidiendo todos los datos obligatorios.

## Nombres de mesas

En **Mesas › Nombres de mesas** se guarda el santo de cada mesa según su número, para hombres y para mujeres. Casi nunca cambia, así que sirve para todos los retiros:

- cuando se crea una mesa sin nombre (en Llegada al escribir el puesto, o con «+ Agregar mesa»), ya llega con el nombre de su número;
- si se agrega una mesa nueva, se escribe su santo en el renglón de su número; dejar un renglón vacío quita ese nombre;
- la casilla «Poner también estos nombres…» se los pone de una vez a las mesas del retiro actual que están sin nombre (no cambia las que ya tienen).
- Cada nombre puede tener su **imagen**: los diez santos de este retiro ya traen una (de dominio público). Para un santo nuevo, se sube con «Subir» en su renglón.
- **Impresiones › Rótulos de mesa (santo y número)** saca las tiras de 30 × 4,4 cm para pegar en la madera de cada mesa: una cara con la imagen y el nombre del santo y la otra con «Mesa N.º». Van dos mesas por hoja oficio horizontal, con líneas punteadas para cortar. Como la Hoja de sacramentos, está hecha para la impresora de Secretaría (papel de 13 pulgadas aunque la impresora diga 14). Con `?mesas=1,3` salen solo esas mesas.

## Buscar personas

Todos los buscadores (Llegada, Inscritos, Pagos, Directivos, Historial, Documentos, Inventario y el buscador general) **no distinguen tildes ni mayúsculas**: «cesar», «CÉSAR» y «César» encuentran a la misma persona, y «munoz» encuentra a «Muñoz».

## Impresiones

- **Quien no tiene mesa y silla no hizo el retiro**: solo sale en la **Lista de llamado** y en **Tesorería**, que se imprimen antes de dar los puestos. No aparece ni se cuenta en ninguna otra hoja.
- Cada hoja tiene tres botones: **Hombres**, **Mujeres** y **Ambos**. «Ambos» no mezcla los grupos: imprime primero lo de hombres y después lo de mujeres, cada uno en sus propias hojas, para no tener que mandar a imprimir dos veces.
- **Carpetas del rectorado** (rector, vicerrector, dirigente): cada carpeta sale completa con su rótulo al frente. Con «Ambos» sale la carpeta de hombres entera y luego la de mujeres. El segundo vicerrector tiene un enlace aparte, sin la evaluación de equipos.
- **Listado de invitados para puertas**: arriba los **directivos** (rector, vicerrector, dirigente responsable y catequistas rotativos) con su ciudad; en cada mesa, primero el **CT** y el **CA** con su ciudad y barrio y después los invitados. La ciudad y el barrio de los catequistas se escriben en **Mesas**; la de los directivos, en **Impresiones › Datos para imprimir**. Si ya se imprimió el listado sin ellos, la dirección `impresiones/puertas?sexo=hombres&servidores=1` (o `mujeres`) saca solo directivos y catequistas, en el mismo formato.
- **Directorio**: al pie lleva las perseverancias (Cuarto día y Escuela destacados, cada grupo con su barrio) y, en el mismo recuadro a la derecha, el **QR del grupo de WhatsApp** del movimiento, para no quitarle espacio a las mesas. El QR se cambia en **Ajustes** (siempre es el mismo; no hace falta tocarlo). Las perseverancias se editan en el menú **Perseverancias**. El **Cuarto día** está arriba en esa misma página, en un recuadro vino tinto aparte: solo fecha, hora y lugar (todo opcional; lo que quede vacío sale como línea para escribir a mano). Sale únicamente en el directorio, nunca en el carrusel ni en el mapa del inicio.
- **Lista sencilla · floristería, secretaría, sobres**: una lista sencilla para pasar lista. Arriba van los **directivos** y luego cada **mesa** con su CT, su CA y las sillas en orden, cada nombre con una casilla para marcar. Va en dos columnas, así cabe cada grupo en una hoja. Arriba dice en grande HOMBRES o MUJERES.
- **Cumpleaños**: quienes cumplen años desde 5 días antes del retiro hasta 5 días después: retiristas (con su mesa y silla), directivos y catequistas, en orden de fecha y con los años que cumplen. Los que cumplen durante el retiro salen resaltados con ★. Hombres y mujeres van juntos en una sola lista (con una columna que dice el grupo) y al final quedan renglones en blanco para escribir a mano a quien falte. El cumpleaños de los directivos se escribe en **Impresiones › Datos para imprimir** y el de los catequistas en **Mesas**; al pie de la hoja salen los que todavía no lo tienen.
- **Hoja 3 · Correcciones** se corta por mesa, por la línea punteada, y cada catequista recibe la de su mesa:
  - cada recorte lleva su propio membrete pequeño: logo, número del retiro de hombres o mujeres, fechas y el número y nombre de la mesa, así queda completo al cortarlo;
  - arriba de la tabla, en rojo: «CATEQUISTA: POR FAVOR VERIFICAR QUE USTED Y TODOS LOS DE SU MESA LLENEN LA INFORMACIÓN COMPLETA»;
  - en las filas del CT y el CA sale lo que ya está en **Mesas** (cumpleaños, ciudad, barrio y celular); lo que falta, más el documento, la talla y los contactos, queda en blanco para que lo escriban;
  - trae la talla de cada persona;
  - lo que se corrija se pasa después a la aplicación en Llegada.
- **Cartas al presbítero**: una por cada persona con mesa y silla (no a quien se fue), hombres primero y luego mujeres, en orden de mesa y silla. La carta anuncia sola la fecha y el lugar del próximo retiro. Si todavía no se ha creado, la página avisa, y si se imprime de todas formas, en vez de la fecha dice «Estaremos anunciando con anticipación la fecha y el lugar de nuestro próximo retiro de conversión simultáneo». El párrafo de los cupos para la parroquia sale siempre.
- **Sacramentos (dinámica)** está hecha para el papel oficio de la impresora de Secretaría. Si una hoja oficio horizontal se corta a la izquierda, el motivo suele ser que la impresora tiene puesto papel de 14 pulgadas y el papel real es de 13.
- Para descargar cualquier hoja en PDF: **Imprimir** y elegir **Guardar como PDF**. Conviene quitar «Encabezado y pie de página» en «Ver más».

- **Hoja para cartas**: dos cartas de media hoja en una hoja carta horizontal, con línea de corte punteada en la mitad, para escribir cartas a mano; con el logo arriba, el logo grande como marca de agua muy suave y ramitas de olivo. Tiene dos botones: con renglones y sin renglones. No depende del retiro.

## Letreros

- Los letreros de **bebidas** (hombres y mujeres) piden escribir la mesa y la silla del invitado: «M 1 – S 1».
- **Ayudas de Dios · caja** es para la caja donde se dejan las cartas. Tiene el mismo estilo del letrero de Ayudas de Dios (el del horario) y pide marcar cada carta con la mesa y la silla del retirista: «M 1 – S 1».

## Horario del Santísimo

- En **Santísimo › Reparto** se guardan:
  - cuántas personas tiene cada equipo;
  - sus horas fijas;
  - las horas en las que no se le puede programar.
- El botón **Repartir** arma el horario solo, respetando esas reglas.
- Con el retiro ya andando, **solo se reparten las horas que todavía no empiezan**: lo que ya pasó no se cambia.
- Las franjas de TODOS (hora santa, Eucaristía con laudes, hora nona, laudes) no se asignan a ningún equipo.

## Datos personales

Este repositorio es **público**. Nunca se suben datos de personas reales: nombres, cédulas, teléfonos o comprobantes. Las pruebas usan datos inventados. Los datos reales viven solo en la base de Secretaría y en el repositorio privado de respaldos (ver [RESPALDO.md](RESPALDO.md)).
