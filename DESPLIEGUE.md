# Subir la aplicación a Hostinger

Guía paso a paso para poner la app de Retiros Juan XXIII en un hosting compartido de Hostinger. Se hace una sola vez; al final está cómo actualizarla.

## 0. Lo que hace falta

- Un plan de Hostinger **con acceso SSH** (Premium, Business o superior) y el dominio ya conectado.
- **PHP 8.2 o más nuevo, con los parches de seguridad de octubre de 2026.** Se cambia en hPanel → Avanzado → Configuración de PHP. Laravel 12 no funciona con PHP 8.1.
  - Por la falla **PHP2Shell** (CVE-2026-109618 y CVE-2026-109619), que permite tomar el control del servidor sin iniciar sesión, la versión debe ser **al menos 8.2.35, 8.3.36, 8.4.27 u 8.5.12**, según la rama. Es mejor la 8.3 o la 8.4 más reciente.
  - Comprobarlo por SSH con `php -v`. Ojo: la versión que ve el sitio web es la de hPanel y puede ser distinta de la de SSH. Si alguna de las dos es más vieja, no subir la app todavía.
  - Si Hostinger todavía no ofrece una versión corregida, poner en la configuración de PHP de hPanel `memory_limit = -1` y `session.upload_progress.enabled = Off`. Es la mitigación oficial mientras llega el parche; no reemplaza actualizar.
- En esa misma pantalla de PHP, revisar:
  - Extensiones activas: `pdo_mysql`, `mbstring`, `fileinfo`, `zip`, `gd`.
  - `upload_max_filesize` en al menos **20M**, porque los archivos de charlas y letreros pueden pesar hasta 20 MB.
  - `post_max_size` en al menos **64M**.

## 1. Crear la base de datos

En hPanel → Bases de datos → Bases de datos MySQL:

1. Crear la base de datos, el usuario y la contraseña.
2. Anotar los tres datos: Hostinger les agrega un prefijo, por ejemplo `u123456789_juanxxiii`.

## 2. Preparar el paquete en el computador

Comprimir en un `.zip` la carpeta `C:\xampp\htdocs\JuanXXIII` **sin** estas partes:

- `vendor/` (se instala en el servidor)
- `.env` (en el servidor va uno propio)
- `.git/`
- el contenido de `storage/logs/`, `storage/framework/cache/`, `storage/framework/sessions/` y `storage/framework/views/`. Las carpetas sí deben ir, aunque vayan vacías.
- `storage/app/private/comprobantes/`: son los comprobantes de las inscripciones de prueba.

## 3. Subir y descomprimir

Con el Administrador de archivos de hPanel o por SFTP:

1. Subir el `.zip` a `domains/TU-DOMINIO/` (al lado de `public_html`, **no** dentro).
2. Descomprimirlo en una carpeta `juanxxiii`. La estructura queda así:

```
domains/TU-DOMINIO/
├── juanxxiii/        ← la aplicación (app, config, public, storage…)
└── public_html/      ← lo que se ve en internet
```

## 4. Hacer que el dominio muestre solo la carpeta `public`

Esto es lo más importante para la seguridad. Si internet viera toda la carpeta, se podrían leer las contraseñas del `.env`.

Entrar por SSH (los datos están en hPanel → Avanzado → Acceso SSH) y correr:

```bash
cd ~/domains/TU-DOMINIO
mv public_html public_html_respaldo
ln -s juanxxiii/public public_html
```

Así `public_html` pasa a ser un acceso directo a `juanxxiii/public`. El `.htaccess` de la raíz del proyecto solo se usa en XAMPP; aquí no hace falta.

## 5. Instalar y configurar

Seguir por SSH:

```bash
cd ~/domains/TU-DOMINIO/juanxxiii
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Abrir `.env` con `nano .env` y dejar así estas líneas (con los datos del paso 1):

```
APP_NAME="Retiros Juan XXIII"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU-DOMINIO
APP_LOCALE=es
APP_FAKER_LOCALE=es_CO

LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u123456789_juanxxiii
DB_USERNAME=u123456789_usuario
DB_PASSWORD=la-contraseña-de-la-base

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

`APP_DEBUG=false` es obligatorio: con `true`, cualquier error le mostraría al visitante datos internos.

### Correo de confirmación

Para que salga el correo de confirmación de cupo, primero hay que crear una cuenta de correo del dominio en hPanel (**Correos**), por ejemplo `retiros@TU-DOMINIO`. Después se agregan estas líneas al `.env`, con esa cuenta y su contraseña:

```
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=retiros@TU-DOMINIO
MAIL_PASSWORD=la-contraseña-del-correo
MAIL_FROM_ADDRESS=retiros@TU-DOMINIO
MAIL_FROM_NAME="Retiros Juan XXIII"
```

Mientras `MAIL_MAILER` siga en `log`, los correos no salen: solo quedan anotados en `storage/logs`. El texto del correo, y si se manda solo al inscribirse, se cambian en la app, en **Ajustes**.

## 6. Crear las tablas y la configuración inicial

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan retiro:crear-admin
```

- `db:seed` carga solo la configuración:
  - roles y equipos;
  - firmantes de la carta y perseverancias;
  - horario base del Santísimo y desarrollo base del retiro.
- En producción **no** crea cuentas demo ni datos de prueba.
- `retiro:crear-admin` crea la primera cuenta de Administración (por ejemplo ADMIN TI). Pide la contraseña sin mostrarla, y la contraseña no queda escrita en ningún archivo. Las demás cuentas se crean después desde la app, en el menú **Cuentas**.

## 7. Permisos y optimización

```bash
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 8. Subir los documentos, charlas y letreros

1. Subir las carpetas tal como están en el computador, por ejemplo a `~/cargas/charlas`, `~/cargas/documentos` y `~/cargas/letreros`.
2. Cargarlas a la app:

```bash
cd ~/domains/TU-DOMINIO/juanxxiii
php artisan archivos:importar ~/cargas/charlas charlas
php artisan archivos:importar ~/cargas/documentos documentos
php artisan archivos:importar ~/cargas/letreros letreros
```

3. Cuando termine, borrar `~/cargas`: la app guarda su propia copia en un lugar privado.

## 9. Comprobar

- [ ] `https://TU-DOMINIO` abre la página de inicio con los botones de inscribirse e iniciar sesión.
- [ ] `https://TU-DOMINIO/.env` da **error 404** (no debe mostrar nada).
- [ ] La versión de PHP del sitio es una corregida (ver el paso 0). En hPanel → Avanzado → Configuración de PHP se ve la versión que usa la web.
- [ ] El candado de HTTPS aparece. Si no, activar el SSL gratuito en hPanel → Seguridad → SSL.
- [ ] Se puede entrar con la cuenta creada en el paso 6.
- [ ] Al crear un retiro y abrir las inscripciones, el formulario público funciona y deja subir un comprobante.
- [ ] Una impresión de prueba sale con el logo.

## Actualizar a una versión nueva

1. Subir los archivos que cambiaron (o un `.zip` nuevo con las mismas exclusiones del paso 2) **sin tocar** `.env` ni `storage/`.
2. Por SSH:

```bash
cd ~/domains/TU-DOMINIO/juanxxiii
php artisan down
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

`db:seed` se puede repetir sin problema: solo agrega lo que falte y nunca cambia lo que ya se editó desde la app.

## Copias de seguridad

Hostinger hace copias automáticas según el plan (hPanel → Archivos → Copias de seguridad). Antes de cada retiro y antes de cada actualización conviene sacar una propia:

```bash
cd ~
mysqldump -u u123456789_usuario -p u123456789_juanxxiii > respaldo-$(date +%F).sql
tar -czf archivos-$(date +%F).tar.gz domains/TU-DOMINIO/juanxxiii/storage/app/private
```

Descargar los dos archivos al computador. Los comprobantes de pago y los documentos están en `storage/app/private`: sin esa carpeta, la base de datos sola no basta.

## Antes del primer retiro real (si se usó la base de pruebas)

Si en vez de empezar con una base nueva se sube la base que se usó para probar, hay que limpiarla primero:

```bash
php artisan retiro:empezar-en-limpio
php artisan retiro:empezar-en-limpio --confirmar --cuentas=prueba.flores
```

- La primera línea solo **muestra** lo que se borraría.
- La segunda borra de verdad (pide escribir BORRAR):
  - retiros, personas, inscripciones, pagos y comprobantes;
  - mesas e inventario;
  - las tareas "(prueba)" y las cuentas `@demo.test`.
- Se conservan la configuración, las cuentas reales y los archivos.
- No corre si con eso no quedara ninguna cuenta activa de Administración.
