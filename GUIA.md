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

## Buscar personas

Todos los buscadores (Llegada, Inscritos, Pagos, Directivos, Historial, Documentos, Inventario y el buscador general) **no distinguen tildes ni mayúsculas**: «cesar», «CÉSAR» y «César» encuentran a la misma persona, y «munoz» encuentra a «Muñoz».

## Impresiones

- Cada hoja tiene tres botones: **Hombres**, **Mujeres** y **Ambos**. «Ambos» no mezcla los grupos: imprime primero lo de hombres y después lo de mujeres, cada uno en sus propias hojas, para no tener que mandar a imprimir dos veces.
- **Carpetas del rectorado** (rector, vicerrector, dirigente): cada carpeta sale completa con su rótulo al frente. Con «Ambos» sale la carpeta de hombres entera y luego la de mujeres. El segundo vicerrector tiene un enlace aparte, sin la evaluación de equipos.
- **Listado de invitados para puertas**: arriba los **directivos** (rector, vicerrector, dirigente responsable y catequistas rotativos) con su ciudad; en cada mesa, primero el **CT** y el **CA** con su ciudad y barrio y después los invitados. La ciudad y el barrio de los catequistas se escriben en **Mesas**; la de los directivos, en **Impresiones › Datos para imprimir**. Si ya se imprimió el listado sin ellos, la dirección `impresiones/puertas?sexo=hombres&servidores=1` (o `mujeres`) saca solo directivos y catequistas, en el mismo formato.
- **Cumpleaños**: quienes cumplen años desde 5 días antes del retiro hasta 5 días después: retiristas (con su mesa y silla), directivos y catequistas, en orden de fecha y con los años que cumplen. Los que cumplen durante el retiro salen resaltados con ★. Hombres y mujeres en hojas separadas. El cumpleaños de los directivos se escribe en **Impresiones › Datos para imprimir** y el de los catequistas en **Mesas**; al pie de la hoja salen los que todavía no lo tienen.
- **Hoja 3 · Correcciones** se corta por mesa, por la línea punteada, y cada catequista recibe la de su mesa:
  - cada recorte lleva su propio membrete pequeño: logo, número del retiro de hombres o mujeres, fechas y el número y nombre de la mesa, así queda completo al cortarlo;
  - arriba de la tabla, en rojo: «CATEQUISTA: POR FAVOR VERIFICAR QUE USTED Y TODOS LOS DE SU MESA LLENEN LA INFORMACIÓN COMPLETA»;
  - en las filas del CT y el CA sale lo que ya está en **Mesas** (cumpleaños, ciudad, barrio y celular); lo que falta, más el documento, la talla y los contactos, queda en blanco para que lo escriban;
  - trae la talla de cada persona;
  - lo que se corrija se pasa después a la aplicación en Llegada.
- **Sacramentos (dinámica)** está hecha para el papel oficio de la impresora de Secretaría. Si una hoja oficio horizontal se corta a la izquierda, el motivo suele ser que la impresora tiene puesto papel de 14 pulgadas y el papel real es de 13.
- Para descargar cualquier hoja en PDF: **Imprimir** y elegir **Guardar como PDF**. Conviene quitar «Encabezado y pie de página» en «Ver más».

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
