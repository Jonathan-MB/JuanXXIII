# Pruebas de la aplicación

Cómo comprobar que todo funciona antes de un retiro o después de un cambio. Hay dos partes:

1. **Pruebas automáticas**: la computadora revisa sola las reglas importantes en menos de un minuto.
2. **Recorrido a mano**: lo que hace cada equipo durante el retiro, en orden, para revisarlo en pantalla y en papel.

En el retiro normalmente hay **un solo PC y lo maneja Secretaría**: imprime para todos (Tesorería, rectorado y equipos). Por eso todo tiene opción de imprimir y la cuenta de Secretaría entra a todo.

---

## 1. Pruebas automáticas

Usan su propia base de datos, `juanxxiii_pruebas`, que se borra y se vuelve a crear en cada prueba. **Nunca tocan la base del retiro.**

La primera vez, crear esa base:

```bash
/c/xampp/mysql/bin/mysql.exe -uroot -e "CREATE DATABASE IF NOT EXISTS juanxxiii_pruebas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

Correrlas (MySQL de XAMPP encendido):

```bash
cd /c/xampp/htdocs/JuanXXIII && /c/xampp/php/php.exe artisan test
```

Debe terminar en `Tests: 20 passed`. Si alguna sale con ⨯, el nombre de la prueba dice qué regla se rompió.

| Archivo | Qué revisa |
|---|---|
| `tests/Feature/SillasTest.php` | Quien abandona conserva su silla y nadie más la ocupa; quien no asistió no conserva puesto; un puesto se corrige sin mover a los demás; dos personas no quedan en la misma silla; en las listas impresas y en el mapa de Llegada la silla de quien se fue queda vacía. |
| `tests/Feature/TesoreriaYLlamadasTest.php` | La lista de pagos trae pagado, falta, medio y padrino con teléfono. Las llamadas traen los dos contactos y el padrino. |
| `tests/Feature/HojasDelRetiroTest.php` | Hojas 1, 2 y 3; «Editar datos» en Llegada corrige contactos sin perder el fijo; membretes y oraciones (una por invitado, con el número del retiro); carta solo a quien terminó, con la fecha del último día y el aviso si falta el próximo retiro; previstos con falda solo en mujeres; Secretaría puede imprimir todo y Tesorería no entra a las impresiones de Secretaría. |

El escenario de prueba (retiro, cuentas, personas con contactos, padrino y puesto) se arma en `tests/EscenarioRetiro.php`. Para una prueba nueva se copia una de las que existen y se cambia lo que se revisa.

---

## 2. Recorrido a mano

### Antes de empezar

- **Datos de prueba**: para tener personas en todos los estados, mesas, pagos e inventario:
  ```bash
  cd /c/xampp/htdocs/JuanXXIII && /c/xampp/php/php.exe artisan db:seed --class=DatosPruebaSeeder
  ```
  Se reconocen por las cédulas que empiezan por `990`, los correos `@demo.test` y «(prueba)» en los catequistas. Se quitan **todos** con:
  ```bash
  cd /c/xampp/htdocs/JuanXXIII && /c/xampp/php/php.exe artisan datos-prueba:quitar
  ```
  No borra el retiro real, su rectorado, desarrollo, Santísimo, archivos ni las cuentas `admin`, `secretaria`, `tesoreria` y `cocina`.
- **Cuentas demo en local**: `admin`, `secretaria`, `tesoreria`, `cocina`, todas con la clave `demo12345`. Con los datos de prueba hay además una por equipo (`montaje`, `coro`, `libreria`…).
- **Para revisar el papel**: en cada hoja, «Imprimir» → «Guardar como PDF», y mirar que no se corte nada y que no salgan hojas de más.

### Días antes (Secretaría)

| Paso | Dónde | Qué debe pasar |
|---|---|---|
| Crear el retiro y el próximo | Retiros | Con números de hombres y mujeres, fechas, lugar y ofrenda. Si no hay próximo, la carta lo avisa. |
| Rectorado y firmas | Impresiones → Datos para imprimir | Salen en directorio, informes y carta. |
| Desarrollo y Santísimo | Desarrollo / Santísimo → copiar la base | Las impresiones del desarrollo, charlistas, sala y Santísimo dejan de decir «no está preparado». |
| Lista de tareas | Cronograma → «Imprimir la lista para marcar a mano» | Por día, con la fecha real del retiro y una casilla por tarea. |
| Letreros de la casa | Letreros | Todos con logo y número del retiro: silencio, baños, Nequi, Ayudas de Dios, bebidas, mesas… |
| Membretes y oraciones | Impresiones | Uno por invitado activo; arriba se puede cambiar la cantidad. Membrete en oficio con «RETIRO N» y fechas; oraciones dos por hoja con número, lugar y fecha. |
| Entrega de previstos | Impresiones | Una tabla por mesa; la falda solo en mujeres; sin mesas salen 10 en blanco. |

### Viernes: llegada

| Paso | Quién | Dónde | Qué debe pasar |
|---|---|---|---|
| Lista de llegada y de llamado | Secretaría | Llegada o Impresiones | En orden alfabético, por hombres y mujeres. |
| Lista para Tesorería | Secretaría para Tesorería | Pagos → «Ver e imprimir lista de pagos» | Por persona: celular, padrino con teléfono, pagado, falta, **medio** y casillas «Terminó de pagar» / «No asistió». |
| Marcar quién llegó | Secretaría | Llegada | El botón marca la llegada; se puede deshacer. |
| Dar mesa y silla (al azar) | Secretaría | Llegada → mesa y silla de cada persona | No deja poner a dos en la misma silla. La mesa se crea sola. |
| Hoja 1 · Nombres | Secretaría → salas | Llegada o Impresiones | En blanco, dos mesas por hoja: cada uno escribe su nombre. Con ella se corrigen los nombres en «Editar datos». |
| Hoja 2 · Datos | Secretaría → salas | Llegada o Impresiones | En blanco, una mesa por hoja horizontal: celular, ciudad, barrio y dos contactos de emergencia. Se corrige en Llegada → «Editar datos», que ahora tiene también los dos contactos y el teléfono del padrino. |
| Hoja 3 · Correcciones | Secretaría → salas | Llegada o Impresiones | Ya impresa con todo lo corregido: documento, fecha, ciudad, barrio, celular y contactos. Es la última revisión. |

### Durante el retiro

| Paso | Quién | Qué debe pasar |
|---|---|---|
| Alguien se va | Secretaría → Inscritos → ficha → estado «Abandonó» | **Conserva su mesa y silla**; su silla queda vacía. En el mapa de Llegada sale en gris como «se fue». No sale en las listas ni recibe carta. Si se intenta poner a otra persona en esa silla, la app lo impide y dice por qué. |
| Corregir un puesto mal escrito | Secretaría → Llegada | Se cambia solo esa persona; nadie más se mueve. |
| Llamadas familiares | Secretaría | Por mesa: invitado, sus dos contactos (parentesco, celular, fijo) y una fila para el padrino de retiro, por si nadie contesta. Casilla «Llamó». |
| Carpetas del rectorado | Secretaría | Rector, vicerrector y dirigente: informes con los nombres del rectorado y el número del retiro. |
| Rótulos de sobres, flores, camisetas, ubicación, catequistas, directorio | Secretaría | Todo con logo, número y fechas del retiro. |
| Inventario | Cada equipo o Secretaría | El impreso dice de qué retiro es: artículo, hay, comprar, conteo y observaciones. |

### Domingo: cierre

| Paso | Quién | Qué debe pasar |
|---|---|---|
| Marcar que terminaron | Secretaría → Llegada (el último día) | Quien tiene mesa y silla queda como «terminó». Quien se fue no. |
| Cartas al presbítero | Secretaría → Impresiones | Una por quien terminó, en orden de mesa y silla. Fecha: **Cali y el domingo en que termina el retiro**, aunque se imprima antes. Anuncia sola el próximo retiro; si no está creado, avisa antes de imprimir. |
| Listas para familiares y puertas | Secretaría | Por mesa y silla. |
| Respaldo | Quien maneja el PC | `/c/JuanXXIII-documentos/respaldar.sh` (sube la base y los archivos al repositorio privado). |

### Cada rol: qué ve

| Cuenta | Qué debe poder hacer |
|---|---|
| Secretaría | Todo lo del retiro e imprimir todo: listas, hojas, carpetas, letreros, pagos, Santísimo, desarrollo, inventario de cualquier equipo y cronograma. No entra a «Cuentas». |
| Tesorería | Pagos: registrar, corregir, ver quién debe e imprimir la lista. No entra a las impresiones de Secretaría. |
| Equipo (cocina, montaje…) | Su inventario, el horario del Santísimo y sus tareas. |
| Directivos | Solo consulta: inscritos y desarrollo del retiro. |
| Administración | Todo, más crear y editar cuentas. |
