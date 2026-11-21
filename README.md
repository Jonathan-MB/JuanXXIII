# Juan XXIII

Aplicación web del Movimiento de Retiros Parroquiales Juan XXIII – Arquidiócesis de Cali: inscripciones, pagos, mesas, llegada, inventario por equipo, horario del Santísimo, desarrollo del retiro, cronograma, impresiones y un buscador general (menú «Buscar» o la caja de arriba) que encuentra hojas, letreros, archivos, personas, mesas y charlas aunque las palabras estén mal escritas.

Hecha en Laravel 12 (PHP 8.2+ y MariaDB/MySQL).

## Instalar en local (XAMPP)

1. `composer install`
2. Copiar `.env.example` a `.env` y ajustar la base de datos.
3. `php artisan key:generate`
4. `php artisan migrate --seed` (solo en una instalación nueva **sin datos**; para recuperar la de Secretaría, ver [RESPALDO.md](RESPALDO.md)). En la terminal de Git, agregar `< /dev/null` al final si se queda esperando.
5. `php artisan retiro:crear-admin` para la primera cuenta de administración.

Para usarla durante el retiro (Llegada, agregar a quien llega sin inscribirse, impresiones, Santísimo), ver [GUIA.md](GUIA.md).

Para publicarla en Hostinger, ver [DESPLIEGUE.md](DESPLIEGUE.md).

Para respaldar la base y los archivos, y recuperar todo si se daña el computador, ver [RESPALDO.md](RESPALDO.md).

Para probar que todo funciona (pruebas automáticas y recorrido del retiro por cada equipo), ver [PRUEBAS.md](PRUEBAS.md).
