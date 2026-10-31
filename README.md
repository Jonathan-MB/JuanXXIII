# Juan XXIII

Aplicación web del Movimiento de Retiros Parroquiales Juan XXIII – Arquidiócesis de Cali: inscripciones, pagos, mesas, llegada, inventario por equipo, horario del Santísimo, desarrollo del retiro, cronograma e impresiones.

Hecha en Laravel 12 (PHP 8.2+ y MariaDB/MySQL).

## Instalar en local (XAMPP)

1. `composer install`
2. Copiar `.env.example` a `.env` y ajustar la base de datos.
3. `php artisan key:generate`
4. `php artisan migrate --seed`
5. `php artisan retiro:crear-admin` para la primera cuenta de administración.

Para publicarla en Hostinger, ver [DESPLIEGUE.md](DESPLIEGUE.md).
